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
        if (!empty($corrector_key = $this->context->getCurrentCorrectorKey())) {
            $this->setFrontendParam('Corrector', $corrector_key);
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
     * Get a pdf from a written essay
     * @see \Edutiek\LongEssayAssessmentService\Writer\Service::getProcessedTextAsPdf
     * 
     * @param DocuItem    $item
     * @param string|null $forCorrectorKey
     * @return string
     */
    public function getWritingAsPdf(DocuItem $item) : string
    {
        $task = $item->getWritingTask();
        $essay = $item->getWrittenEssay();

        $pdfParts = [];

        if (!empty($itemPages = $this->context->getPagesOfItem($item->getKey()))) {
            foreach ($itemPages as $itemPage) {
                $image = $this->context->getPageImage($itemPage->getKey());
                $path = $this->getPageImagePathForPdf($image);
                $pdfParts[] = (new PdfPart(
                    PdfPart::FORMAT_A4,
                    PdfPart::ORIENTATION_PORTRAIT
                ))->withPrintHeader(true)
                  ->withPrintFooter(true)
                  ->withElement(new PdfImage(
                      $path,
                      0,0, 210,297     // A5
                  ));
            }
        }
        else {
            $pdfParts[] = (new PdfPart(
                PdfPart::FORMAT_A4,
                PdfPart::ORIENTATION_PORTRAIT
            ))->withElement(
                new PdfHtml($this->dependencies->html()->processWrittenText($essay, $this->context->getWritingSettings())));
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
        }
        
        foreach ($item->getCorrectionSummaries() as $summary) {
            if (!isset($forCorrectorKey) || $summary->getCorrectorKey() == $forCorrectorKey) {
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionResult($item, $summary));
                if ($summary->getIncludeComments()) {
                    $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionContent($item, $summary));
                }
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionText($item, $summary));
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
    protected function getPdfCorrectionResult(DocuItem $item, CorrectionSummary $summary) : array
    {
        $renderContext = [
            'corrector_name' =>  $summary->getCorrectorName(),
            'is_authorized' => $summary->isAuthorized(),
            'last_change' => $this->formatDates($summary->getLastChange()),
            'points' => $summary->getPoints(),
            'grade_title' => $summary->getGradeTitle(),
            'include_comments' => $summary->getIncludeComments() > CorrectionSummary::INCLUDE_NOT,
            'include_comments_info' => $summary->getIncludeComments() == CorrectionSummary::INCLUDE_INFO,
            'include_comments_relevant' => $summary->getIncludeComments() == CorrectionSummary::INCLUDE_RELEVANT,
            'include_comment_ratings_info' => $summary->getIncludeCommentRatings() == CorrectionSummary::INCLUDE_INFO,
            'include_comment_ratings_relevant' => $summary->getIncludeCommentRatings() == CorrectionSummary::INCLUDE_RELEVANT,
            'include_comment_points_info' => $summary->getIncludeCommentPoints() == CorrectionSummary::INCLUDE_INFO,
            'include_comment_points_relevant' => $summary->getIncludeCommentPoints() == CorrectionSummary::INCLUDE_RELEVANT,
            'include_criteria_points_info' => $summary->getIncludeCriteriaPoints() == CorrectionSummary::INCLUDE_INFO,
            'include_criteria_points_relevant' => $summary->getIncludeCriteriaPoints() == CorrectionSummary::INCLUDE_RELEVANT,
            'include_writer_notes_info' => $summary->getIncludeWriterNotes() == CorrectionSummary::INCLUDE_INFO,
            'include_writer_notes_relevant' => $summary->getIncludeWriterNotes() == CorrectionSummary::INCLUDE_RELEVANT
        ];

        $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_result_de.html', $renderContext);

        if ($summary->getIncludeCriteriaPoints()) {
            $criteria = $this->context->getRatingCriteria($summary->getCorrectorKey());
            if (!empty($criteria)) {
                $points = [];
                foreach ( $this->context->getCorrectionPoints($item->getKey(), $summary->getCorrectorKey()) as $pointsObj) {
                    $points[$pointsObj->getCriterionKey()] = ($points[$pointsObj->getCriterionKey()] ?? 0) + $pointsObj->getPoints();
                }

                $renderContext = [
                    'criteria' => []
                ];
                foreach ($criteria as $criterion) {
                    $renderContext['criteria'][] = [
                        'title' => $criterion->getTitle(),
                        'description' => $criterion->getDescription(),
                        'points' => $points[$criterion->getKey()] ?? 0
                    ];
                }

                $html .= $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_criteria_de.html', $renderContext);
            }

        }
        
        return [(new PdfPart())->withElement(new PdfHtml($html))];
    }

    /**
     * Get the pdf part with a correctors summary and result
     * @return PdfPart[]
     */
    protected function getPdfCorrectionText(DocuItem $item, CorrectionSummary $summary) : array
    {
        $renderContext = [
            'corrector_name' =>  $summary->getCorrectorName(),
            'text' => $summary->getText()
        ];

        $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_text_de.html', $renderContext);
        return [(new PdfPart())->withElement(new PdfHtml($html))];
    }


    /**
     * Get the pdf part with a correctors comments and marks on the writer content
     * @return PdfPart[]
     */
    protected function getPdfCorrectionContent(DocuItem $item, CorrectionSummary $summary): array
    {
        $pdfParts = [];
        $comments = [];

        $hasCriteria = !empty($this->context->getRatingCriteria($summary->getCorrectorKey()));
        foreach ($item->getCommentsByCorrectorKey($summary->getCorrectorKey()) as $comment) {
           
            // put criteria related points into comment
            if ($hasCriteria) {
                $points = 0;
                foreach($this->context->getCorrectionPoints($comment->getItemKey(), $comment->getCorrectorKey(), $comment->getKey()) as $pointsObject) {
                    $points += $pointsObject->getPoints();
                }
                $comment = $comment->withPoints($points);
            }
            $comments[] = $comment
                ->withShowRating($summary->getIncludeCommentRatings() > CorrectionSummary::INCLUDE_NOT)
                ->withShowPoints($summary->getIncludeCommentPoints() > CorrectionSummary::INCLUDE_NOT);
        }

        
        if (!empty($itemPages = $this->context->getPagesOfItem($item->getKey()))) {
            foreach ($itemPages as $itemPage) {
                $pageComments = $this->dependencies->commentHandling()->getSortedCommentsOfParent($comments, $itemPage->getPageNo());
                $commentsContext = [];
                foreach ($pageComments as $comment) {
                    
                    if ($comment->hasDetailsToShow()) {
                        $commentsContext[] = [
                            'label' => $comment->getLabel(),
                            'text' => $comment->getComment(),
                            'cardinal' => $comment->showRating() && $comment->getRating() == CorrectionComment::RATING_CARDINAL,
                            'excellent' => $comment->showRating() && $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT,
                            'one_point' => $comment->showPoints() && $comment->getPoints() == 1,
                            'points' => $comment->showPoints() ? $comment->getPoints() : 0
                        ];
                    }
                }
                $renderContext= [
                    'page' => [
                        'corrector_name' => $summary->getCorrectorName(),
                        'page_no' => $itemPage->getPageNo(),
                        'comments' => $this->dependencies->commentHandling()->getCommentsHtml($pageComments)
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
            $renderContext= [
                'text' => [
                'comments' => $this->dependencies->html()->processCommentsForPdf($essay, $this->context->getWritingSettings(), $comments)
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