<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\DocuItem;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfPart;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfHtml;
use Edutiek\LongEssayAssessmentService\Internal\Data\PdfImage;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSummary;
use Edutiek\LongEssayAssessmentService\Data\PageImage;

/**
 * API of the LongEssayAssessmentService for an LMS related to the correction of essays
 * @package Edutiek\LongEssayAssessmentService\Corrector
 */
class Service extends Base\BaseService
{
    /**
     * @const Path of the frontend web app, relative to the service root directory, without starting slash
     */
    public const FRONTEND_RELATIVE_PATH = 'node_modules/long-essay-assessment-corrector/dist/index.html';

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
        if (!empty($corrector = $this->context->getCurrentCorrector())) {
            $this->setFrontendParam('Corrector', $corrector->getKey());
        }
        if (!empty($item = $this->context->getCurrentItem())) {
            $this->setFrontendParam('Item', $item->getKey());
        }
        $this->setFrontendParam('IsReview', $this->context->isReview() ? '1' : '0');
        $this->setFrontendParam('IsStitchDecision', $this->context->isStitchDecision() ? '1' : '0');
    }



    /**
     * Handle a REST like request from the LongEssayCorrector Web App
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
     * Get a pdf from a corrected essay
     * If a corrector key is given then only the correction of this corrector is exported
     */
    public function getCorrectionAsPdf(DocuItem $item, string $forCorrectorKey = null) : string
    {
        $pdfParts = [];
        
        $task = $item->getWritingTask();
        $essay = $item->getWrittenEssay();
        
        if (!isset($forCorrectorKey)) {
            $pdfParts = array_merge($pdfParts, $this->getPdfOverview($item));
            $pdfParts = array_merge($pdfParts, $this->getPdfWrittenContent($item));
        }
        
        foreach ($item->getCorrectionSummaries() as $summary) {
            if (!isset($forCorrectorKey) || $summary->getCorrectorKey() == $forCorrectorKey) {
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionSummary($item, $summary));
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionContent($item, $summary));
            }
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
     * Get the pdf part with overview information of corrected essay
     * @return PdfPart[]
     */
    protected function getPdfOverview(DocuItem $item) : array
    {
        $task = $item->getWritingTask();
        $essay = $item->getWrittenEssay();

        $renderContext = [];
        $renderContext['writing'] = [
            'edit_started' =>  $this->formatDates($essay->getEditStarted()),
            'edit_ended' =>  $this->formatDates($essay->getEditEnded()),
            'writing_excluded' => $this->formatDates($task->getWritingExcluded()),
            'is_authorized' => $essay->isAuthorized(),
            'writing_authorized' =>  $this->formatDates($essay->getWritingAuthorized()),
            'writing_authorized_by' => $essay->getWritingAuthorizedBy(),
        ];
        $renderContext['correction'] =  [
            'correction_finalized' => $this->formatDates($essay->getCorrectionFinalized()),
            'correction_finalized_by' => $essay->getCorrectionFinalizedBy(),
            'final_points' => $essay->getFinalPoints(),
            'final_grade' => $essay->getFinalGrade(),
            'stitch_comment' => $essay->getStitchComment()
        ];

        $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/overview_de.html', $renderContext);
        return [(new PdfPart())->withElement(new PdfHtml($html))];
    }

    /**
     * Get the pdf part with a correctors summary and result
     * @return PdfPart[]
     */
    protected function getPdfCorrectionSummary(DocuItem $item, CorrectionSummary $summary) : array
    {
        $renderContext = [
            'corrector_name' =>  $summary->getCorrectorName(),
            'is_authorized' => $summary->isAuthorized(),
            'last_change' => $this->formatDates($summary->getLastChange()),
            'points' => $summary->getPoints(),
            'grade_title' => $summary->getGradeTitle(),
            'text' => $summary->getText()
        ];

        $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_summary_de.html', $renderContext);
        return [(new PdfPart())->withElement(new PdfHtml($html))];
    }


    /**
     * Get the pdf part with the written text
     * @return PdfPart[]
     */
    protected function getPdfWrittenContent(DocuItem $item) : array
    {
        $pdfParts = [];
        if (!empty($itemPages = $this->context->getPagesOfItem($item->getKey()))) {
            foreach ($itemPages as $itemPage) {
                $pageImage = $this->context->getPageImage($itemPage->getKey());
                $pdfParts[] = (new PdfPart(
                    PdfPart::FORMAT_A4,
                    PdfPart::ORIENTATION_PORTRAIT
                ))->withPrintHeader(false)
                  ->withPrintFooter(true)
                  ->withHeaderMargin(0)
                  ->withFooterMargin(0)
                  ->withLeftMargin(0)
                  ->withRightMargin(0)
                  ->withElement(new PdfImage(
                      $this->getPageImagePathForPdf($pageImage),
                      0,0, 210,297       // A4
                        
                  ));
            }
        }
        else {
            $essay = $item->getWrittenEssay();
            $processedText = $this->dependencies->html()->processWrittenText((string) $essay->getWrittenText());
            $renderContext = [
                'text' => [$this->dependencies->html()->processTextForPdf($processedText)
            ]];
            $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/writer_content_de.html', $renderContext);
            $pdfParts[] = (new PdfPart())->withElement(new PdfHtml($html));
        }
        return $pdfParts;
    }


    /**
     * Get the pdf part with a correctors comments and marks on the writer content
     * @return PdfPart[]
     */
    protected function getPdfCorrectionContent(DocuItem $item, CorrectionSummary $summary): array
    {
        $pdfParts = [];
        $comments = $item->getCommentsByCorrectorKey($summary->getCorrectorKey());
        
        if (!empty($itemPages = $this->context->getPagesOfItem($item->getKey()))) {
            foreach ($itemPages as $itemPage) {
                $pageComments = $this->getSortedCommentsOfParent($comments, $itemPage->getPageNo());
                $commentsContext = [];
                foreach ($pageComments as $comment) {
                    $commentsContext[] = [
                      'label' => $comment->getLabel(),
                      'text' => $comment->getComment(),
                      'cardinal' => $comment->getRating() == CorrectionComment::RATING_CARDINAL,
                      'excellent' => $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT
                    ];
                }
                $renderContext= [
                    'page' => [
                        'corrector_name' => $summary->getCorrectorName(),
                        'page_no' => $itemPage->getPageNo(),
                        'comments' => $commentsContext
                    ]];

                $image = $this->context->getPageImage($itemPage->getKey());
                $path = '';
                if (isset($image)) {
                    $commented = $this->dependencies->image()->applyCommentsMarks($itemPage, $image, $pageComments);
                    $path = $this->getPageImagePathForPdf($commented);
                }
                $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_content_de.html', $renderContext);

                $pdfParts[] = (new PdfPart(
                    PdfPart::FORMAT_A4,
                    PdfPart::ORIENTATION_LANDSCASPE
                ))->withPrintHeader(false)
                  ->withPrintFooter(true)
                  ->withElement(new PdfImage(
                      $path,
                        0,0, 148,210     // A5
                    ))
                  ->withElement(new PdfHtml(
                      $html,
                      150
                  ));
            }
        }
        else {
            $essay = $item->getWrittenEssay();
            $processedText = $this->dependencies->html()->processWrittenText((string) $essay->getWrittenText());
            $renderContext= [
                'text' => [
                'comments' => $this->dependencies->html()->processCommentsForPdf($processedText, $comments)
            ]];
            $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_content_de.html', $renderContext);
            $pdfParts[] = (new PdfPart(
                PdfPart::FORMAT_A4,
                PdfPart::ORIENTATION_PORTRAIT
            ))->withElement(new PdfHtml(
                $html
            ));
        }
        
        return $pdfParts;
    }
    
    /**
     * @param CorrectionComment[] $comments
     * @param int   $parent_no
     * @return CorrectionComment[]
     */
    protected function getSortedCommentsOfParent(array $comments, int $parent_no) : array
    {
        $sort = [];
        foreach($comments as $comment) {
            if ($comment->getParentNumber() == $parent_no) {
                $key = sprintf('%06d', $comment->getStartPosition()) . $comment->getKey();
                $sort[$key] = $comment;
            }
        }
        ksort($sort);
        
        $result = [];
        $number = 1;
        foreach ($sort as $comment) {
            // only comments with text or rating should get a label
            // others are only marks
            if (!empty($comment->getComment() || !empty($comment->getRating()))) {
                $result[] = $comment->withLabel($parent_no . '.' . $number++);
            }
            else {
                $result[] = $comment;
            }
        }
        
        return $result;
    }

    /**
     * Get the path of a writer page image for pdf processing
     * @param PageImage|null $image
     * @return string
     */
    protected function getPageImagePathForPdf(?PageImage $image) : string
    {
        if (isset($image)) {
            return $this->dependencies->image()->getImageSrcAsPathForTCPDF(
                $image,
                $this->context->getAbsoluteTempPath(),
                $this->context->getRelativeTempPath()
            );
        }
        return '';
    }
}