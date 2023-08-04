<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;

use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Base\BaseContext;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSummary;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use Edutiek\LongEssayAssessmentService\Data\CorrectionPoints;

/**
 * Handler of REST requests from the corrector app
 */
class Rest extends Base\BaseRest
{
    /** @var Context  */
    protected $context;


    /**
     * Init server / add handlers
     * @param Context $context
     * @param Dependencies $dependencies
     */
    public function init(BaseContext $context, Dependencies $dependencies)
    {
        parent::init($context, $dependencies);
        $this->get('/data', [$this,'getData']);
        $this->get('/item/{key}', [$this,'getItem']);
        $this->get('/file/{key}', [$this,'getFile']);
        $this->get('/image/{item_key}/{key}', [$this,'getPageImage']);
        $this->put('/changes/{key}', [$this, 'putChanges']);
        $this->put('/summary/{key}', [$this, 'putSummary']);
        $this->put('/stitch/{key}', [$this, 'putStitchDecision']);
    }

    /**
     * @inheritDoc
     * here: set mode for review or stitch decision
     */
    protected function prepare(Request $request, Response $response, array $args, string $purpose): bool
    {
        if (parent::prepare($request, $response, $args, $purpose)) {
            try {
                $this->context->setReview((bool) $this->params['LongEssayIsReview']);
                $this->context->setStitchDecision((bool) $this->params['LongEssayIsStitchDecision']);
                return true;
            }
            catch (ContextException $e) {
                $this->setResponseForContextException($e);
                return false;
            }
        }
        return false;
    }

    /**
     * GET the data for the correction task
     * @param Request  $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $task = $this->context->getCorrectionTask();
        $settings = $this->context->getCorrectionSettings();

        $resources = [];
        foreach ($this->context->getResources() as $resource) {
            $resources[] = [
                'key' => $resource->getKey(),
                'title' => $resource->getTitle(),
                'type' => $resource->getType(),
                'source' => $resource->getSource(),
                'mimetype' => $resource->getMimetype(),
                'size' => $resource->getSize()
            ];
        }
        $levels = [];
        foreach ($this->context->getGradeLevels() as $level) {
            $levels[] = [
                'key' => $level->getKey(),
                'title' => $level->getTitle(),
                'min_points' => $level->getMinPoints()
            ];
        }
        $criteria = [];
        foreach ($this->context->getRatingCriteria() as $criterion) {
            $criteria[] = [
                'key' => $criterion->getKey(),
                'corrector_key' => $criterion->getCorrectorKey(),
                'title' => $criterion->getTitle(),
                'description' => $criterion->getDescription(),
                'points' => $criterion->getPoints()
            ];
        }
        $items = [];
        foreach ($this->context->getCorrectionItems(true) as $item) {
            $items[] = [
                'key' => $item->getKey(),
                'title' => $item->getTitle()
            ];
        }

        $json = [
            'task' => [
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
                'solution' => $task->getSolution(),
                'correction_end' => $task->getCorrectionEnd(),
                'correction_allowed' => false,      // will be set when item is loaded
                'authorization_allowed' => false    // will be set when item is loaded
            ],
            'settings' => [
                'mutual_visibility' => $settings->hasMutualVisibility(),
                'multi_color_highlight' => $settings->hasMultiColorHighlight(),
                'max_points' => $settings->getMaxPoints(),
                'max_auto_distance' => $settings->getMaxAutoDistance(),
                'stitch_when_distance' => $settings->getStitchWhenDistance(),
                'stitch_when_decimals' => $settings->getStitchWhenDecimals()
            ],
            'resources' => $resources,
            'levels' => $levels,
            'criteria' => $criteria,
            'items' => $items
        ];

        $this->setNewDataToken();
        $this->setNewFileToken();
        return $this->setResponse(StatusCode::HTTP_OK, $json);
    }


    /**
     * GET the data of a correction item
     * @param Request  $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getItem(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        // corrector can be null in review and stitch decision
        $currentCorrector = $this->context->getCurrentCorrector();
        $currentCorrectorKey = isset($currentCorrector) ? $currentCorrector->getKey() : '';

        foreach ($this->context->getCorrectionItems() as $item) {

            if ($item->getKey() == $args['key']) {

                $essay = $this->context->getEssayOfItem($item->getKey());
                // update the processed text if needed
                // after authorization the written text will not change
                if (!empty($essay)) {
                    $processed = $this->dependencies->html()->processWrittenText($essay->getWrittenText());
                    if ($processed != $essay->getProcessedText()) {
                        $this->context->setProcessedText($item->getKey(), $processed);
                        $essay = $essay->withProcessedText($processed);
                    }
                }

                $pages = [];
                foreach ($this->context->getPagesOfItem($item->getKey()) as $page) {
                    $pages[] = [
                        'key' => $page->getKey(),
                        'item_key' => $page->getItemKey(),
                        'page_no' => $page->getPageNo(),
                        'width' => $page->getWidth(),
                        'height' => $page->getHeight(),
                        'thumb_width' => $page->getThumbWidth(),
                        'thumb_height' => $page->getThumbHeight()
                    ];
                }
                
                $correctors = [];
                $comments = [];
                $points = [];
                foreach ($this->context->getCorrectorsOfItem($item->getKey()) as $corrector) {

                    // PILOT style: comments and points are commonly transmitted for current and other correctors
                    foreach ($this->context->getCorrectionComments($item->getKey(), $corrector->getKey()) as $comment) {
                        $comments[] = [
                            'key' => $comment->getKey(),
                            'item_key' => $item->getKey(),
                            'corrector_key' => $corrector->getKey(),
                            'start_position' => $comment->getStartPosition(),
                            'end_position' => $comment->getEndPosition(),
                            'parent_number' => $comment->getParentNumber(),
                            'comment' => $comment->getComment(),
                            'points' => $comment->getPoints(),
                            'rating' => $comment->getRating()
                        ];
                    }
                    foreach ($this->context->getCorrectionPoints($item->getKey(), $corrector->getKey()) as $point) {
                        $points[] = [
                            'key' => $point->getKey(),
                            'comment_key' => $point->getCommentKey(),
                            'criterion_key' => $point->getCriterionKey(),
                            'points' => $point->getPoints()
                        ];
                    }

                    // PRE-TEST style: summaries are provided separately for other correctors
                    if ($corrector->getKey() == $currentCorrectorKey) {
                        continue;
                    }
                    $summary = $this->context->getCorrectionSummary($item->getKey(), $corrector->getKey());
                    if (isset($summary) && $summary->isAuthorized()) {
                        $correctors[] = [
                            'key' => $corrector->getKey(),
                            'title' => $corrector->getTitle(),
                            'text' => $summary->getText(),
                            'points' => $summary->getPoints(),
                            'grade_key' => $summary->getGradeKey(),
                            'last_change' => $summary->getLastChange(),
                            'is_authorized' => $summary->isAuthorized(),
                        ];
                    }
                    else {
                        // don't provide date of other corrector if not yet authorized
                        // but provide corrector to see the authorized status
                        $correctors[] = [
                            'key' => $corrector->getKey(),
                            'title' => $corrector->getTitle(),
                            'text' => null,
                            'points' => null,
                            'grade_key' => null,
                            'last_change' => null,
                            'is_authorized' => false
                        ];
                    }
                }
                $task = $this->context->getCorrectionTask();
                
                if (!empty($currentCorrectorKey)) {
                    $summary = $this->context->getCorrectionSummary($item->getKey(), $currentCorrectorKey);
                }

                $json = [
                    'task' => [
                        'title' => $task->getTitle(),
                        'instructions' => $task->getInstructions(),
                        'solution' => $task->getSolution(),
                        'correction_end' => $task->getCorrectionEnd(),
                        'correction_allowed' => $item->isCorrectionAllowed(),
                        'authorization_allowed' => $item->isAuthorizationAllowed()
                    ],
                    'essay' => [
                        'text'=> isset($essay) ? $essay->getProcessedText() : null,
                        'started' => isset($essay) ? $essay->getEditStarted() : null,
                        'ended' => isset($essay) ? $essay->getEditEnded() : null,
                        'authorized' => isset($essay) ? $essay->isAuthorized() : null
                    ],
                    'pages' => $pages,
                    'correctors' => $correctors,
                    'comments' => $comments,
                    'points' => $points,
                    'summary' => [
                        'text' => isset($summary) ? $summary->getText() : null,
                        'points' => isset($summary) ? $summary->getPoints() : null,
                        'grade_key' => isset($summary) ? $summary->getGradeKey() : null,
                        'last_change' => isset($summary) ? $summary->getLastChange() : null,
                        'is_authorized' => isset($summary) && $summary->isAuthorized()
                    ],
                ];

                $this->refreshDataToken();
                return $this->setResponse(StatusCode::HTTP_OK, $json);
            }
        }

        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'item not found');
    }


    /**
     * PUT the summary of a correction item
     * @param Request  $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function putChanges(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }
        $data = $this->request->getParsedBody();

        $currentCorrector = $this->context->getCurrentCorrector();
        $currentCorrectorKey = isset($currentCorrector) ? $currentCorrector->getKey() : '';

        $comment_matching = [];
        foreach ((array) $data['comments'] as $key => $cdata) {
            
            if (isset($cdata)) {
                $comment = new CorrectionComment(
                    (string) $cdata['key'],
                    (string) $cdata['item_key'],
                    (string) $cdata['corrector_key'],
                    (int) $cdata['start_position'],
                    (int) $cdata['end_position'],
                    (int) $cdata['parent_number'],
                    (string) $cdata['comment'],
                    (string) $cdata['rating'],
                    (int) $cdata['points'],
                );

                if (!empty($id = $this->context->saveCorrectionComment($comment, $currentCorrectorKey))) {
                    $comment_matching[$key] = (string) $id;
                }
            }
            elseif ($this->context->deleteCorrectionComment($key, $currentCorrectorKey)) {
                $comment_matching[$key] = null;
            }
        }

        $points_matching = [];
        foreach ((array) $data['points'] as $key => $pdata) {
            if (isset($pdata)) {
                $points = new CorrectionPoints(
                    (string) $pdata['key'],
                    (string) ($comment_matching[$pdata['comment_key']] ?? $pdata['comment_key']),
                    (string) $pdata['criterion_key'],
                    (int) $pdata['points']
                );
                
                if (!empty($id = $this->context->saveCorrectionPoints($points, $currentCorrectorKey))) {
                    $points_matching[$key] = (string) $id;
                }
                elseif ($this->context->deleteCorrectionPoints($key, $currentCorrectorKey)) {
                    $points_matching[$key] = null;
                }
            }
        }
        
        $json = [
          'comments' => $comment_matching,
          'points' => $points_matching
        ];

        $this->refreshDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK, $json);
    }

    
    /**
     * PUT the summary of a correction item
     * @param Request  $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function putSummary(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }
        $data = $this->request->getParsedBody();

        $currentCorrector = $this->context->getCurrentCorrector();
        $currentCorrectorKey = isset($currentCorrector) ? $currentCorrector->getKey() : '';

        foreach ($this->context->getCorrectionItems() as $item) {
            if ($item->getKey() == $args['key']) {
                foreach ($this->context->getCorrectorsOfItem($item->getKey()) as $corrector) {
                    if ($corrector->getKey() == $currentCorrectorKey) {
                        $summary = new CorrectionSummary(
                            (string) $item->getKey(),
                            (string) $currentCorrectorKey,
                            isset($data['text']) ? (string) $data['text'] : null,
                            isset($data['points']) ? (float) $data['points'] : null,
                            isset($data['grade_key']) ? (string) $data['grade_key'] : null,
                            isset($data['last_change']) ? (int) $data['last_change'] : time(),
                            isset($data['is_authorized']) ? (bool) $data['is_authorized'] : null,
                        );
                        $this->context->setCorrectionSummary($item->getKey(), $currentCorrectorKey, $summary);
                        $this->refreshDataToken();
                        $this->context->setAlive();
                        return $this->setResponse(StatusCode::HTTP_OK);
                    }
                }
                return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'current user is no corrector');
            }
        }
        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'item not found');
    }


    /**
     * PUT the summary of a correction item
     * @param Request  $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function putStitchDecision(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }
        $data = $this->request->getParsedBody();

        if ($this->context->saveStitchDecision(
            (string) $args['key'],
            (int) $data['correction_finalized'],
            isset($data['final_points']) ? (float) $data['final_points'] : null,
            !empty($data['grade_key']) ? (string) $data['grade_key'] : null,
            !empty($data['stitch_comment']) ? (string) $data['stitch_comment'] : null
            )) {
            $this->refreshDataToken();
            $this->context->setAlive();
            return $this->setResponse(StatusCode::HTTP_OK);
        }
        return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'not saved');
    }


    /**
     * GET a page image
     */
    public function getPageImage(Request $request, Response $response, array $args): Response
    {
        
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_FILE)) {
            return $this->response;
        }

        $key = (string) ($args['key'] ?? '');
        $item_key = (string) ($args['item_key'] ?? '');

        // todo: check if allowed for corrector

        foreach ($this->context->getPagesOfItem($item_key) as $page) {
            if ($page->getKey() == $key) {
                $this->context->sendPageImage($page->getKey());
                return $response;
            }
        }

        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'resource not found');
    }

}
