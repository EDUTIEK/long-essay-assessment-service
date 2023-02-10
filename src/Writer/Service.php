<?php

namespace Edutiek\LongEssayAssessmentService\Writer;
use DiffMatchPatch\DiffMatchPatch;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\WritingStep;

/**
 * API of the LongEssayAssessmentService for an LMS related to the writing of essays
 * @package Edutiek\LongEssayAssessmentService\Writer
 */
class Service extends Base\BaseService
{
    /**
     * @const Path of the frontend web app, relative to the service root directory, without starting slash
     */
    public const FRONTEND_RELATIVE_PATH = 'node_modules/long-essay-writer/dist/index.html';

    /** @var Context */
    protected $context;

    /**
     * Service constructor.
     * A class implementing the Context interface must be provided by the LMS for this service
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function setSpecificFrontendParams()
    {
        // add the hash of the current essay content
        // this will be used to check if the writer content is outdated

        $essay = $this->context->getWrittenEssay();
        $this->setFrontendParam('Hash', (string) $essay->getWrittenHash());
    }

    /**
     * Handle a REST like request from the LongEssayWriter Web App
     * @throws \Throwable
     */
    public function handleRequest()
    {
        $server = new Rest(
            [
                'settings' => [
                    'displayErrorDetails' => true
                ]
            ]
        );

        $server->init($this->context, $this->dependencies);
        $server->run();
    }


    /**
     * Get a pdf from the text that has been processed for the corrector
     */
    public function getProcessedTextAsPdf() : string
    {
        $task = $this->context->getWritingTask();
        $essay = $this->context->getWrittenEssay();

        return $this->dependencies->pdfGeneration()->generatePdfFromHtml(
            $this->dependencies->html()->processWrittenText($essay->getWrittenText()),
            $this->context->getSystemName(),
            $task->getWriterName(),
            $task->getTitle(),
            $task->getWriterName() . ' ' . $this->formatDates($essay->getEditStarted(), $essay->getEditEnded())
        );
    }



    /**
     * Get the HTML diff of a writing step applied to a text
     */
    public function getWritingDiffHtml(string $before, WritingStep $step) : string
    {
        $after = $this->getWritingDiffResult($before, $step);
        $dmp = new DiffMatchPatch();
        $diffs = $dmp->diff_main($before, $after);
        $dmp->diff_cleanupEfficiency($diffs);
        return $dmp->diff_prettyHtml($diffs);
    }

    /**
     * Get the result of a writing step
     */
    public function getWritingDiffResult(string $before, WritingStep  $step) : string
    {
        $dmp = new DiffMatchPatch();
        if ($step->isDelta()) {
            $patches = $dmp->patch_fromText($step->getContent());
            $result = $dmp->patch_apply($patches, $before);
            $after = $result[0];
        }
        else {
            $after = $step->getContent();
        }

        return $after;
    }
}