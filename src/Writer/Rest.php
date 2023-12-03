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
use Edutiek\LongEssayAssessmentService\Data\WrittenNote;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Data\WritingTask;
use Edutiek\LongEssayAssessmentService\Data\WritingPreferences;

/**
 * Handler of REST requests from the writer app
 */
class Rest extends Base\BaseRest
{
    /** @var Context  */
    protected $context;

    /** @var WritingTask */
    protected $task;

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
        $this->put('/changes', [$this, 'putChanges']);
        $this->put('/final', [$this,'putFinal']);
    }

    /**
     * @inheritDoc
     * here: set mode for review or stitch decision
     */
    protected function prepare(Request $request, Response $response, array $args, string $purpose): bool
    {
        if (parent::prepare($request, $response, $args, $purpose)) {
            try {
                $this->task = $this->context->getWritingTask();
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
     * GET the data for initializing the writer
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_DATA)) {
            return $this->response;
        }

        $settings = $this->context->getWritingSettings();
        $preferences = $this->context->getWritingPreferences();
        $task = $this->context->getWritingTask();
        $essay = $this->context->getWrittenEssay();


        $notes = [];
        foreach ($this->context->getWrittenNotes() as $note) {
            $notes[] = [
                'note_no' => $note->getNoteNo(),
                'note_text' => $note->getNoteText(),
                'last_change' => $note->getLastChange()
            ];
        }

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
            'preferences' => [
                'instructions_zoom' => $preferences->getInstructionsZoom(),
                'editor_zoom' => $preferences->getEditorZoom()
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
            'notes' => $notes,
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

        $this->refreshDataToken();
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

        $this->refreshDataToken();
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

        $this->refreshDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK);
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

        $body = $this->request->getParsedBody();
        $essay = $this->context->getWrittenEssay();

        $max_time = null;
        $notes_done = [];
        $preferences_done = [];

        // Save notes

        foreach ((array) $body['notes'] as $change) {
            if (!$this->areChangesAllowed()) {
                continue;
            }

            switch ($change['action'] ?? '') {
                case 'save':
                    if (!empty(($data = $change['payload'] ?? null))) {
                        if (!$this->isChangeTimeAllowed((int) $change['server_time'])) {
                            continue 2;
                        }
                        
                        $note = new WrittenNote(
                            (int) $data['note_no'],
                            isset($data['note_text']) ? ((string) $data['note_text']) : null,
                            (int) $change['server_time']
                        );
                        $this->context->setWrittenNote($note);
                        $notes_done[$change['key']] = $change['key'];

                        $max_time = max($max_time ?? 0, (int) $change['server_time']);
                    }
                    break;
            }
        }
        
        // save preferences (only one with fixed key 
        foreach ((array) $body['preferences'] as $change) {
            if (!empty($data = $change['payload'] ?? null)) {
                $preferences = new WritingPreferences(
                    (float) $data['instructions_zoom'],
                    (float) $data['editor_zoom'],
                );
                $this->context->setWritingPreferences($preferences);
                $preferences_done[$change['key']] = $change['key'];
            }
        }

        // Touch the summaries for changed comments or points 
        // This sets the last change and ensures that a summary exists
        if (isset($max_time) && $max_time > ($essay->getEditEnded() ?? 0)) {
            $this->context->setWrittenEssay($essay->withEditEnded($max_time));
        }
        
        $json = [
            'notes' => $notes_done,
            'preferences' => $preferences_done,
        ];

        $this->refreshDataToken();
        $this->context->setAlive();
        return $this->setResponse(StatusCode::HTTP_OK, $json);
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

        $this->refreshDataToken();
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
        $this->context->deleteWrittenNotes();
    }

    /**
     * Check if changes are generally allowed
     */
    protected function areChangesAllowed() : bool
    {
        $essay = $this->context->getWrittenEssay();
        if ($essay->isAuthorized()) {
            return false;
        }
        return true;
    }

    /**
     * Check if a chane time is allowed
     */
    protected function isChangeTimeAllowed(int $timestamp) : bool
    {
        if (!empty($this->task->getWritingEnd()) && $this->task->getWritingEnd() < $timestamp) {
            return false;
        }
        return true;
    }

}