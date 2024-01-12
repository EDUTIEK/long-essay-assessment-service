<?php

namespace Edutiek\LongEssayAssessmentService\Writer;
use DiffMatchPatch\DiffMatchPatch;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\WritingStep;
use Edutiek\LongEssayAssessmentService\Data\PageImage;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfPart;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfHtml;

/**
 * API of the LongEssayAssessmentService for an LMS related to the writing of essays
 * @package Edutiek\LongEssayAssessmentService\Writer
 */
class Service extends Base\BaseService
{
    /**
     * @const Path of the frontend web app, relative to the service root directory, without starting slash
     */
    public const FRONTEND_RELATIVE_PATH = 'node_modules/long-essay-assessment-writer/dist/index.html';

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
     * This PDF is intended to document the writing. It has a header and footer
     * @see \Edutiek\LongEssayAssessmentService\Corrector\Service::getWritingAsPdf
     */
    public function getProcessedTextAsPdf() : string
    {
        $task = $this->context->getWritingTask();
        $essay = $this->context->getWrittenEssay();

        $part = (new PdfPart(
            PdfPart::FORMAT_A4,
            PdfPart::ORIENTATION_PORTRAIT
        ))->withElement(
            new PdfHtml($this->dependencies->html()->processWrittenText($essay, $this->context->getWritingSettings())));

        return $this->dependencies->pdfGeneration()->generatePdf(
            [$part],
            $this->context->getSystemName(),
            $task->getWriterName(),
            $task->getTitle(),
            $task->getWriterName() . ' ' . $this->formatDates($essay->getEditStarted(), $essay->getEditEnded())
        );
    }

    /**
     * Get a plain pdf (without header/hooter) from the text that has been processed for the corrector
     * This PDF can be converted to an image for graphical marking and commenting
     */
    public function getProcessedTextAsPlainPdf() : string
    {
        $essay = $this->context->getWrittenEssay();
        $settings = $this->context->getWritingSettings();

        $style = file_get_contents(__DIR__ . '/templates/plain_style.html');
        $html = $this->dependencies->html()->processWrittenText($essay, $settings);

        $part = (new PdfPart(
            PdfPart::FORMAT_A4,
            PdfPart::ORIENTATION_PORTRAIT,
            [new PdfHtml($style . $html)]
        ))
            ->withTopMargin($settings->getTopMargin())
            ->withBottomMargin($settings->getBottomMargin())
            ->withLeftMargin($settings->getLeftMargin())
            ->withRightMargin($settings->getRightMargin())
            ->withHeaderMargin(0)
            ->withFooterMargin(10)
            ->withPrintHeader(false)
            ->withPrintFooter(true);

        return $this->dependencies->pdfGeneration()->generatePdf([$part]);
    }

    /**
     * Get the html the text that has been processed for the corrector
     */
    public function getProcessedTextAsHtml() : string
    {
        $essay = $this->context->getWrittenEssay();
        return  $this->dependencies->html()->processWrittenText($essay, $this->context->getWritingSettings());
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

    /**
     * Create the page images from pdf files
     * @param resource[] $pdfs - file handlers of pdf files
     * @return PageImage[]
     */
    public function createPageImagesFromPdfs(array $pdfs) : array 
    {
        $images = [];
        foreach ($pdfs as $pdf) {
            $images = array_merge($images,  $this->dependencies->image()->createImagesFromPdf($pdf));
        }
        return $images;
    }
}
