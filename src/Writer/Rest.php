<?php

namespace Edutiek\LongEssayAssessmentService\Writer;

use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Base\BaseContext;
use Edutiek\LongEssayAssessmentService\Data\WritingStep;
use Edutiek\LongEssayAssessmentService\Data\WrittenEssay;
use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use DiffMatchPatch\DiffMatchPatch;

/**
 * Handler of REST requests from the writer app
 */
class Rest extends Base\BaseRest
{
    /** @var Context  */
    protected $context;


    /**
     * Init server / add handlers
     */
    public function init(BaseContext $context, Dependencies $dependencies)
    {
        parent::init($context, $dependencies);
        $this->get('/data', [$this,'getData']);
        $this->get('/update', [$this,'getUpdate']);
        $this->get('/file/{key}', [$this,'getFile']);
        $this->put('/start', [$this,'putStart']);
        $this->put('/steps', [$this,'putSteps']);
        $this->put('/final', [$this,'putFinal']);
    }

    /**
     * GET the data for initializing the writer
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $settings = $this->context->getWritingSettings();
        $task = $this->context->getWritingTask();
        $essay = $this->context->getWrittenEssay();

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

        $steps = [];
        // send all steps if undo should be based on them
        // then each step would need a revert diff
        // currently undo from tiny is used - no need to send the steps
//        foreach ($this->context->getWritingSteps(null) as $step) {
//            $steps[] = [
//              'timestamp' => $step->getTimestamp(),
//              'content' => $step->getContent(),
//              'is_delta' => $step->isDelta(),
//              'hash_before' => $step->getHashBefore(),
//              'hash_after' => $step->getHashAfter()
//            ];
//        }

        $json = [
            'settings' => [
                'headline_scheme' => $settings->getHeadlineScheme(),
                'formatting_options' => $settings->getFormattingOptions(),
                'notice_boards' => $settings->getNoticeBoards(),
                'copy_allowed' => $settings->isCopyAllowed(),
                'primary_color' => $settings->getPrimaryColor(),
                'primary_text_color' => $settings->getPrimaryTextColor()
            ],
            'task' => [
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
                'writer_name' => $task->getWriterName(),
                'writing_end' => $task->getWritingEnd()
            ],
            'essay' => [
                'content' => $essay->getWrittenText(),
                'hash' => $essay->getWrittenHash(),
                'started' => $essay->getEditStarted(),
                'authorized' => $essay->isAuthorized(),
                'steps' => $steps,
            ],
            'resources' => $resources,
        ];

        $this->setNewDataToken();
        $this->setNewFileToken();
        return $this->setResponse(StatusCode::HTTP_OK, $json);
    }

    /**
     * GET the data for updating the writer
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $task = $this->context->getWritingTask();

        $alerts = [];
        foreach ($this->context->getAlerts() as $alert) {
            $alerts[] = [
                'message' => $alert->getMessage(),
                'time' => $alert->getTime(),
                'key' => $alert->getKey()
            ];
        }

        $json = [
            'task' => [
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
                'writer_name' => $task->getWriterName(),
                'writing_end' => $task->getWritingEnd(),
                'writing_excluded' => $task->getWritingExcluded()
            ],
            'alerts' => $alerts
        ];

        $this->setNewDataToken();
        // don't set a new file token - it should not expire
        return $this->setResponse(StatusCode::HTTP_OK, $json);
    }


    /**
     * PUT the writing start timestamp
     */
    public function putStart(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $data = $this->request->getParsedBody();
        if (!isset($data['started']) || !is_int($data['started'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'start timestamp expected');
        }

        $essay = $this->context->getWrittenEssay();
        if (!empty($essay->getEditStarted()))
        {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'start is already set');
        }

        $essay = $essay->withEditStarted($data['started']);
        $this->context->setWrittenEssay($essay);

        $this->setNewDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK);
    }


    /**
     * PUT a list of writing steps
     */
    public function putSteps(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $data = $this->request->getParsedBody();
        if (!isset($data['steps']) || !is_array($data['steps'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'list of steps expected');
        }

        $essay = $this->context->getWrittenEssay();
        $this->saveWritingSteps($essay, $data['steps']);

        $this->setNewDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK);
    }


    /**
     * PUT the final content
     * "final" means that the writer is intentionally closed
     * That could be an interruption or the authorized submission
     * the content is only saved when the essay is not yet authorized
     */
    public function putFinal(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $data = $this->request->getParsedBody();
        if (!isset($data['steps']) || !is_array($data['steps'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'list of steps expected');
        }
        if (!isset($data['content'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'content expected');
        }
        if (!isset($data['hash'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'hash expected');
        }
        if (!isset($data['authorized'])) {
            return $this->setResponse(StatusCode::HTTP_BAD_REQUEST, 'authorization expected');
        }

        $essay = $this->context->getWrittenEssay();
        if (!$essay->isAuthorized()) {
            $this->saveWritingSteps($essay, $data['steps']);
            $this->context->setWrittenEssay($essay
                ->withWrittenText((string) $data['content'])
                ->withWrittenHash((string) $data['hash'])
                ->withProcessedText(null) // processing may cause html parsing errors, do not at saving
                ->withIsAuthorized((bool) $data['authorized'])
            );
        }

        $this->setNewDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK);
    }


    /**
     * Save a list of writing steps
     */
    protected function saveWritingSteps(WrittenEssay $essay, array $data)
    {
        $dmp = new DiffMatchPatch();

        $currentText = $essay->getWrittenText();
        $currentHash = $essay->getWrittenHash();

        $steps = [];
        foreach($data as $entry) {
            $step = new WritingStep(
                $entry['timestamp'],
                $entry['content'],
                $entry['is_delta'],
                $entry['hash_before'],
                $entry['hash_after']
            );

            // check if step can be added
            // fault tolerance if a former put was partially applied or the response to the app was lost
            // then this list may include steps that are already saved
            // exclude these steps because they will corrupt the sequence
            // later steps may fit again
            if ($step->getHashBefore() !== $currentHash) {
                if ($step->isDelta()) {
                    // don't add a delta step that can't be applied
                    // step may already be saved, so a later new step may fit
                    continue;
                }
                elseif ($this->context->hasWritingStepByHashAfter($step->getHashAfter())) {
                    // the same full save should not be saved twice
                    // note: hash_after is salted by timestamp and is unique
                    continue;
                }
            }
            $steps[] = $step;

            if ($step->isDelta()) {
                $patches = $dmp->patch_fromText($step->getContent());
                $result = $dmp->patch_apply($patches, $currentText);
                $currentText = $result[0];
            }
            else {
                $currentText = $step->getContent();
            }
            $currentHash = $step->getHashAfter();
        }

        // save the data
        $this->context->addWritingSteps($steps);
        $this->context->setWrittenEssay($essay
            ->withWrittenText($currentText)
            ->withWrittenHash($currentHash)
            ->withEditEnded(isset($step) ? $step->getTimestamp() : null)
            ->withProcessedText(null) // processing may cause html parsing errors, do not at saving
        );
    }
}