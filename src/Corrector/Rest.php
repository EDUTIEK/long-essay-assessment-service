<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;

use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Base\BaseContext;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSummary;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\StatusCode;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use Edutiek\LongEssayAssessmentService\Data\CorrectionPoints;
use Edutiek\LongEssayAssessmentService\Data\CorrectionMark;
use Edutiek\LongEssayAssessmentService\Data\CorrectionPreferences;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSettings;

/**
 * Handler of REST requests from the corrector app
 */
class Rest extends Base\BaseRest
{
    /** @var Context  */
    protected $context;

    /**
     * Key of the current corrector (if not review or stitch decision mode)
     * @var ?string
     */
    protected $currentCorrectorKey = null;

    /**
     * Flags for items whether changes are allowed by the current corrector
     * @var bool[] item_key => is assigned
     */
    protected $changesAllowedCache = [];
    
    /**
     * Init server / add handlers
     * @param Context $context
     * @param Dependencies $dependencies
     */
    public function run()
    {
        $this->app->get('/data', [$this,'getData']);
        $this->app->get('/item/{key}', [$this,'getItem']);
        $this->app->get('/file/{key}', [$this,'getFile']);
        $this->app->get('/image/{item_key}/{key}', [$this,'getPageImage']);
        $this->app->get('/thumb/{item_key}/{key}', [$this,'getPageThumb']);
        $this->app->put('/changes/{key}', [$this, 'putChanges']);
        $this->app->put('/stitch/{key}', [$this, 'putStitchDecision']);
        $this->app->run();
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
                $this->currentCorrectorKey = $this->context->getCurrentCorrectorKey();
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

        // corrector can be null in review and stitch decision
        if (empty($this->currentCorrectorKey) && !$this->context->isReview() && !$this->context->isStitchDecision()) {
            return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'getting correction data is not allowed');
        }

        $task = $this->context->getCorrectionTask();
        $settings = $this->context->getCorrectionSettings();
        $preferences = $this->context->getCorrectionPreferences($this->currentCorrectorKey);

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
                'points' => $criterion->getPoints(),
                'is_general' => $criterion->getIsGeneral()
            ];
        }
        $items = [];
        foreach ($this->context->getCorrectionItems(true) as $item) {
            $items[] = [
                'key' => $item->getKey(),
                'title' => $item->getTitle(),
                'correction_allowed' => $item->isCorrectionAllowed(),
                'authorization_allowed' => $item->isAuthorizationAllowed()
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
                'stitch_when_decimals' => $settings->getStitchWhenDecimals(),
                'positive_rating' => $settings->getPositiveRating(),
                'negative_rating' => $settings->getNegativeRating(),
                'headline_scheme' => $settings->getHeadlineScheme(),
                'fixed_inclusions' => $settings->hasFixedInclusions(),
                'include_comments' => $settings->getIncludeComments(),
                'include_comment_ratings' => $settings->getIncludeCommentRatings(),
                'include_comment_points' => $settings->getIncludeCommentPoints(),
                'include_criteria_points' => $settings->getIncludeCriteriaPoints()
            ],
            'preferences' => [
                'essay_page_zoom' => $preferences->getEssayPageZoom(),
                'essay_text_zoom' => $preferences->getEssayTextZoom(),
                'summary_text_zoom' => $preferences->getSummaryTextZoom(),
                'include_comments' => $preferences->getIncludeComments(),
                'include_comment_ratings' => $preferences->getIncludeCommentRatings(),
                'include_comment_points' => $preferences->getIncludeCommentPoints(),
                'include_criteria_points' => $preferences->getIncludeCriteriaPoints()
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
        if (empty($this->currentCorrectorKey) && !$this->context->isReview() && !$this->context->isStitchDecision()) {
            return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'getting correction item is not allowed');
        }

        // the items are already filtered for the corrector
        foreach ($this->context->getCorrectionItems() as $item) {

            if ($item->getKey() == $args['key']) {

                $essay = $this->context->getEssayOfItem($item->getKey());

                $pages = [];
                foreach ($this->context->getPagesOfItem($item->getKey()) as $page) {
                    $pages[] = [
                        'key' => $page->getKey(),
                        'item_key' => $item->getKey(),
                        'page_no' => $page->getPageNo(),
                        'width' => $page->getWidth(),
                        'height' => $page->getHeight(),
                        'thumb_width' => $page->getThumbWidth(),
                        'thumb_height' => $page->getThumbHeight()
                    ];
                }
                
                $correctors = [];
                $summaries = [];
                $comments = [];
                $points = [];

                foreach ($this->context->getCorrectorsOfItem($item->getKey()) as $corrector) {

                    // a corrector may be added multiple for several items
                    $correctors[] = [
                        'item_key' => $corrector->getItemKey(),
                        'corrector_key' => $corrector->getCorrectorKey(),
                        'title' => $corrector->getTitle(),
                        'initials' => $corrector->getInitials(),
                        'position' => $corrector->getPosition()
                    ];

                    $summary = $this->context->getCorrectionSummary($item->getKey(), $corrector->getCorrectorKey());
                    if (isset($summary) && ($corrector->getCorrectorKey() == $this->currentCorrectorKey || $summary->isAuthorized())) {
                        // add the existing summary for the current corrector or an authorized summary for other correctors
                        $summaries[] = [
                            'item_key' => $summary->getItemKey(),
                            'corrector_key' => $summary->getCorrectorKey(),
                            'text' => $summary->getText(),
                            'points'  => $summary->getPoints(),
                            'grade_key' => $summary->getGradeKey(),
                            'last_change' => $summary->getLastChange(),
                            'is_authorized' => $summary->isAuthorized(),
                            'include_comments' => $summary->getIncludeComments(),
                            'include_comment_ratings' => $summary->getIncludeCommentRatings(),
                            'include_comment_points' => $summary->getIncludeCommentPoints(),
                            'include_criteria_points' => $summary->getIncludeCriteriaPoints()
                        ];
                    }
                    else {
                        // provide a dummy summary if not existing or nor authorized for other correctors
                        $summaries[] = [
                            'item_key' => $item->getKey(),
                            'corrector_key' => $corrector->getCorrectorKey(),
                            'text' => null,
                            'points' => null,
                            'grade_key' => null,
                            'last_change' => null,
                            'is_authorized' => false,
                            'include_comments' => null,
                            'include_comment_ratings' => null,
                            'include_comment_points' => null,
                            'include_criteria_points' => null
                        ];
                    }
                    
                    // provide comments or points for current corrector or if the corrector's summary is authorized
                    if ($corrector->getCorrectorKey() == $this->currentCorrectorKey || (isset($summary) && $summary->isAuthorized())) {
                        
                        foreach ($this->context->getCorrectionComments($item->getKey(), $corrector->getCorrectorKey()) as $comment) {
                            $comments[] = [
                                'key' => $comment->getKey(),
                                'item_key' => $comment->getItemKey(),
                                'corrector_key' => $comment->getCorrectorKey(),
                                'start_position' => $comment->getStartPosition(),
                                'end_position' => $comment->getEndPosition(),
                                'parent_number' => $comment->getParentNumber(),
                                'comment' => $comment->getComment(),
                                'rating' => $comment->getRating(),
                                'marks' => CorrectionMark::multiToArray($comment->getMarks())
                            ];
                        }
                        foreach ($this->context->getCorrectionPoints($item->getKey(), $corrector->getCorrectorKey()) as $point) {
                            $points[] = [
                                'key' => $point->getKey(),
                                'item_key' => $point->getItemKey(),
                                'corrector_key' => $point->getCorrectorKey(),
                                'comment_key' => $point->getCommentKey(),
                                'criterion_key' => $point->getCriterionKey(),
                                'points' => $point->getPoints()
                            ];
                        }
                    }
                }

                $json = [
                    'item' => [
                        'key' => $item->getKey(),
                        'title' => $item->getTitle(),
                        'correction_allowed' => $item->isCorrectionAllowed(),
                        'authorization_allowed' => $item->isAuthorizationAllowed()
                    ],
                    'essay' => [
                        'text'=> isset($essay) ? $this->dependencies->html()->processWrittenText($essay, $this->context->getWritingSettings()) : null,
                        'started' => isset($essay) ? $essay->getEditStarted() : null,
                        'ended' => isset($essay) ? $essay->getEditEnded() : null,
                        'authorized' => isset($essay) ? $essay->isAuthorized() : null,
                        'correction_finalized' => isset($essay) ? $essay->getCorrectionFinalized() : null,
                        'final_points' => isset($essay) ? $essay->getFinalPoints() : null,
                        'stitch_comment' => isset($essay) ? $essay->getStitchComment() : null,
                    ],
                    'pages' => $pages,
                    'correctors' => $correctors,
                    'summaries' => $summaries,
                    'comments' => $comments,
                    'points' => $points,
                ];

                $this->refreshDataToken();
                return $this->setResponse(StatusCode::HTTP_OK, $json);
            }
        }

        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'item not found');
    }


    /**
     * PUT the unsent changes in the corrector app
     * 
     * This is prepared to handle changes in different correction items
     * The changes are available from the parsed body as assoc arrays with properties:
     * - key: existing or temporary key of the object to be saved
     * - item_key: key of the correction item to which the object belongs
     * - 
     * 
     * The added, changed or deleted data of single comments, points or summaries
     * is wrapped as "payload" in a
     * 
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
        if (empty($this->currentCorrectorKey)) {
            return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'sending changes is not allowed');
        }

        $body = $this->request->getParsedBody();

        /**
         * Last change times item_key => unix time
         */
        $times = [];
        
        $comments_done = [];
        $points_done = [];
        $summaries_done = [];
        $preferences_done = [];

        // Save comments
        
        foreach ((array) $body['comments'] as $change) {
            if (!$this->areChangesAllowed((string) $change['item_key'])) {
                continue;
            }

            switch ($change['action'] ?? '') {
                case 'save':
                    if (!empty(($data = $change['payload'] ?? null))) {
                        if ($data['item_key'] != $change['item_key'] || $data['corrector_key'] != $this->currentCorrectorKey) {
                            continue 2;
                        }

                        $comment = new CorrectionComment(
                            (string) $data['key'],
                            (string) $data['item_key'],
                            (string) $data['corrector_key'],
                            (int) $data['start_position'],
                            (int) $data['end_position'],
                            (int) $data['parent_number'],
                            (string) $data['comment'],
                            (string) $data['rating'],
                            CorrectionMark::multiFromArray((array) ($data['marks'] ?? []))
                        );

                        if (!empty($key = $this->context->saveCorrectionComment($comment))) {
                            $comments_done[$change['key']] = $key;
                            $times[$change['item_key']] = max($times[$change['item_key']] ?? 0, (int) $change['server_time']);
                        }
                    }
                    break;

                case 'delete': 
                    if ($this->context->deleteCorrectionComment((string) $change['key'], $this->currentCorrectorKey)) {
                        $comments_done[$change['key']] = null;
                        $times[$change['item_key']] = max($times[$change['item_key']] ?? 0, (int) $change['server_time']);
                    }
                    break;
            }
        }
        
        // Save points
        
        foreach ((array) $body['points'] as $change) {
            if (!$this->areChangesAllowed((string) $change['item_key'])) {
                continue;
            }

            switch ($change['action'] ?? '') {
                case 'save':
                    if (!empty($data = $change['payload'] ?? null)) {
                        if ($data['item_key'] != $change['item_key']) {
                            continue 2;
                        }
                        $points = new CorrectionPoints(
                            (string) $data['key'],
                            (string) $data['item_key'],
                            $this->currentCorrectorKey,
                            (string) ($comments_done[$data['comment_key']] ?? $data['comment_key']),
                            (string) $data['criterion_key'],
                            (int) $data['points']
                        );

                        if (!empty($key = $this->context->saveCorrectionPoints($points))) {
                            $points_done[$change['key']] = $key;
                            $times[$change['item_key']] = max($times[$change['item_key']] ?? 0, (int) $change['server_time']);
                        }
                    }
                    break;
                
                case 'delete':
                    if ($this->context->deleteCorrectionPoints($change['key'], $this->currentCorrectorKey)) {
                        $points_done[$change['key']] = null;
                        $times[$change['item_key']] = max($times[$change['item_key']] ?? 0, (int) $change['server_time']);
                    }
                    break;
            }
        }

        // Save summaries
        
        foreach ((array) $body['summaries'] as $change) {
            if (!$this->areChangesAllowed((string) $change['item_key'])) {
                continue;
            }
            
            switch ($change['action'] ?? '') {
                case 'save':
                    if (!empty($data = $change['payload'] ?? null)) {
                        if ($data['item_key'] != $change['item_key'] || $data['corrector_key'] != $this->currentCorrectorKey) {
                            continue 2;
                        }
                        
                        $summary = new CorrectionSummary(
                            (string) $data['item_key'],
                            (string) $data['corrector_key'],
                            isset($data['text']) ? (string) $data['text'] : null,
                            isset($data['points']) ? (float) $data['points'] : null,
                            isset($data['grade_key']) ? (string) $data['grade_key'] : null,
                            max($times[$change['item_key']] ?? 0, (int) $change['server_time']),
                            (bool) ($data['is_authorized'] ?? false),
                            isset($data['include_comments']) ? (int) $data['include_comments'] : null,
                            isset($data['include_comment_ratings']) ? (int) $data['include_comment_ratings'] : null,
                            isset($data['include_comment_points']) ? (int) $data['include_comment_points'] : null,
                            isset($data['include_criteria_points']) ? (int) $data['include_criteria_points'] : null
                        );

                        if ($this->context->saveCorrectionSummary($summary)) {
                            $summaries_done[$change['key']] = $change['key'];
                            // last change time is already set
                            unset($times[$change['item_key']]);
                        }
                    }
                    break;
            }
        }

        // save preferences (only one with fixed key 
        foreach ((array) $body['preferences'] as $change) {
            if (!empty($data = $change['payload'] ?? null)) {
                $preferences = new CorrectionPreferences(
                    (string) $this->currentCorrectorKey,
                    (float) $data['essay_page_zoom'],
                    (float) $data['essay_text_zoom'],
                    (float) $data['summary_text_zoom'],
                    (int) $data['include_comments'],
                    (int) $data['include_comment_ratings'],
                    (int) $data['include_comment_points'],
                    (int) $data['include_criteria_points']
                );
                if ($this->context->saveCorrectionPreferences($preferences)) {
                    $preferences_done[$change['key']] = $change['key'];
                    break;
                }
            }
        }

        // Touch the summaries for changed comments or points 
        // This sets the last change and ensures that a summary exists
        foreach ($times as $item_key => $time) {
            $summary = $this->context->getCorrectionSummary((string) $item_key, (string) $this->currentCorrectorKey);
            if (empty($summary)) {
                $summary = new CorrectionSummary((string) $item_key, (string) $this->currentCorrectorKey);
            }
            $this->context->saveCorrectionSummary($summary->withLastChange($time));
        }
        
        
        $json = [
          'comments' => $comments_done,
          'points' => $points_done,
          'summaries' => $summaries_done,
          'preferences' => $preferences_done,
        ];

        $this->refreshDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK, $json);
    }

    

    /**
     * PUT the stitch decision of a correction item
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
        if (!$this->context->isStitchDecision()) {
            return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'stitch decision is not allowed');
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
     * Get a Page thumbnail
     */
    public function getPageThumb(Request $request, Response $response, array $args): Response
    {
        return $this->getPageImage($request, $response, $args, true);
    }

    /**
     * GET a page image
     */
    public function getPageImage(Request $request, Response $response, array $args, bool $thumb = false): Response
    {
        
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_FILE)) {
            return $this->response;
        }

        // corrector can be null in review and stitch decision
        if (empty($this->currentCorrectorKey) && !$this->context->isReview() && !$this->context->isStitchDecision()) {
            return $this->setResponse(StatusCode::HTTP_FORBIDDEN, 'getting page image is not allowed');
        }

        $key = (string) ($args['key'] ?? '');
        $item_key = (string) ($args['item_key'] ?? '');
        
        foreach ($this->context->getPagesOfItem($item_key) as $page) {
            if ($page->getKey() == $key) {
                if ($thumb) {
                    $this->context->sendPageThumb($page->getKey());
                } else {
                    $this->context->sendPageImage($page->getKey());
                }
                
                return $response;
            }
        }

        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'resource not found');
    }

    /**
     * Check if changes are allowed for an item by the current corrector
     * @param string $item_key
     * @return bool
     */
    protected function areChangesAllowed(string $item_key) : bool
    {
        if (empty($this->currentCorrectorKey)) {
            return false;
        }   
        
        if (!isset($this->changesAllowedCache[$item_key])) {
            $this->changesAllowedCache[$item_key] = false;
            if ($this->context->isCorrectorOfItem($item_key, $this->currentCorrectorKey)) {
                $summary = $this->context->getCorrectionSummary($item_key, $this->currentCorrectorKey);
                if (!isset($summary) || !$summary->isAuthorized()) {
                    $this->changesAllowedCache[$item_key] = true;
                }
            }
        }
        
        return $this->changesAllowedCache[$item_key];
    }

}
