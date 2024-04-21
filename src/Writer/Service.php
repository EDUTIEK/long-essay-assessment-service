<?php

namespace Edutiek\LongEssayAssessmentService\Writer;
use DiffMatchPatch\DiffMatchPatch;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\PdfSettings;
use Edutiek\LongEssayAssessmentService\Data\WritingSettings;
use Edutiek\LongEssayAssessmentService\Data\WritingStep;
use Edutiek\LongEssayAssessmentService\Data\PageImage;
use Edutiek\LongEssayAssessmentService\Data\WritingTask;
use Edutiek\LongEssayAssessmentService\Data\WrittenEssay;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfImage;
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
     * Get a combined pdf file from the written text and from an uploaded pdf file
     *
     * @param bool $plainContent get the content without header/footer, for image creation or download by corrector
     */
    public function getWritingAsPdf(WritingTask $task, WrittenEssay $essay, bool $plainContent = false) : string
    {
        if ($plainContent) {
            $pdfSettings = new PdfSettings(false, false, 0, 0, 0, 0);
        }
        else {
            $pdfSettings = $this->context->getPdfSettings();
        }

        $pdfParts = [];
        if (!empty($pages = $this->context->getPagesOfWriter())) {
            foreach ($pages as $page) {
                $image = $this->context->getPageImage($page->getKey());
                $path = $this->getPageImagePathForPdf($image);
                $pdfParts[] = $this->getStandardPdfPart(
                    [new PdfImage($path,
                        $pdfSettings->getLeftMargin(),
                        $pdfSettings->getContentTopMargin(),
                        210 // A4
                        - $pdfSettings->getLeftMargin()-$pdfSettings->getRightMargin(),
                        297 // A4
                        - $pdfSettings->getContentTopMargin()-$pdfSettings->getContentBottomMargin()
                    )],
                    $pdfSettings
                );
            }
        }
        else {
            $writingSettings = $this->context->getWritingSettings();
            $html = $this->dependencies->html()->processWrittenText($essay, $writingSettings, true);
            $pdfParts[] = $this->getStandardPdfPart([
                new PdfHtml($html,
                    $pdfSettings->getLeftMargin() + $writingSettings->getLeftCorrectionMargin(),
                    $pdfSettings->getContentTopMargin(),
                    210 // A4
                        - $pdfSettings->getLeftMargin() - $pdfSettings->getRightMargin()
                        - $writingSettings->getLeftCorrectionMargin() - $writingSettings->getRightCorrectionMargin(),
                    297 // A4
                        - $pdfSettings->getContentTopMargin()- $pdfSettings->getContentBottomMargin()
                )], $pdfSettings
                    ->withTopMargin($pdfSettings->getTopMargin() + $writingSettings->getTopCorrectionMargin())
                    ->withBottomMargin($pdfSettings->getBottomMargin() + $writingSettings->getBottomCorrectionMargin())
            );
        }

        return $this->dependencies->pdfGeneration()->generatePdf(
            $pdfParts,
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

    /**
     * Create the page images from pdf files
     * @param resource[] $pdfs - file handlers of pdf files
     * @param string $path_to_ghostscript ghostscript executable
     * @param string $workdir working directory
     * @return PageImage[]
     */
    public function createPageImagesFromPdfs(array $pdfs, string $path_to_ghostscript = null, string $workdir = null) : array
    {
        $images = [];
        foreach ($pdfs as $pdf) {
            $images = array_merge($images,  $this->dependencies->image()->createImagesFromPdf($pdf, $path_to_ghostscript, $workdir));
        }
        return $images;
    }
}
