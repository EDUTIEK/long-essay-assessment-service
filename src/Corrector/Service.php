<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\DocuItem;
use Edutiek\LongEssayAssessmentService\Data\PdfSettings;
use Edutiek\LongEssayAssessmentService\Data\PdfPart;
use Edutiek\LongEssayAssessmentService\Data\PdfHtml;
use Edutiek\LongEssayAssessmentService\Data\PdfImage;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSummary;

/**
 * API of the LongEssayAssessmentService for an LMS related to the correction of essays
 * @package Edutiek\LongEssayAssessmentService\Corrector
 */
class Service extends Base\BaseService
{
    /**
     * @const node module name of the frontend
     */
    protected const FRONTEND_MODULE = 'long-essay-assessment-corrector';

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
        $server = new Rest($this->context, $this->dependencies);
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
        }
        
        foreach ($item->getCorrectionSummaries() as $summary) {
            if ($summary->isAuthorized() || $summary->getCorrectorKey() == $forCorrectorKey) {
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionResult($item, $summary));
                if ($summary->getIncludeComments()) {
                    $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionContent($item, $summary));
                }
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionText($item, $summary));
            }
            else {
                $pdfParts = array_merge($pdfParts, $this->getPdfCorrectionOpen($item, $summary));
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
        return [$this->getStandardPdfPart([new PdfHtml($html)])->withPrintFooter(false)];
    }

    /**
     * Get a minimal part with a correctors name and the notice that correction is not yet authorized
     * @param DocuItem          $item
     * @param CorrectionSummary $summary
     * @return array
     */
    protected function getPdfCorrectionOpen(DocuItem $item, CorrectionSummary $summary) : array
    {
        $renderContext = [
            'corrector_name' =>  $summary->getCorrectorName(),
        ];
        $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_open_de.html', $renderContext);
        return [$this->getStandardPdfPart([new PdfHtml($html)])->withPrintFooter(false)];
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
            'positive_rating' => $this->context->getCorrectionSettings()->getPositiveRating(),
            'negative_rating' => $this->context->getCorrectionSettings()->getNegativeRating()
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
                        'points' => $points[$criterion->getKey()] ?? 0,
                        'is_general' => $criterion->getIsGeneral()
                    ];
                }

                $html .= $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_criteria_de.html', $renderContext);
            }

        }

        return [$this->getStandardPdfPart([new PdfHtml($html)])->withPrintFooter(false)];
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

        return [$this->getStandardPdfPart([new PdfHtml($html)])->withPrintFooter(false)];
    }


    /**
     * Get the pdf part with a correctors comments and marks on the writer content
     * @return PdfPart[]
     */
    protected function getPdfCorrectionContent(DocuItem $item, CorrectionSummary $summary): array
    {
        $pdfParts = [];
        $comments = [];

        foreach ($item->getCommentsByCorrectorKey($summary->getCorrectorKey()) as $comment) {
           
            // put points into comment for showing
            $points = 0;
            foreach($this->context->getCorrectionPoints($comment->getItemKey(), $comment->getCorrectorKey(), $comment->getKey()) as $pointsObject) {
                $points += $pointsObject->getPoints();
            }
            $comment = $comment->withPoints($points);
            $comments[] = $comment
                ->withShowRating($summary->getIncludeCommentRatings() > CorrectionSummary::INCLUDE_NOT)
                ->withShowPoints($summary->getIncludeCommentPoints() > CorrectionSummary::INCLUDE_NOT);
        }

        
        if (!empty($itemPages = $this->context->getPagesOfItem($item->getKey()))) {
            foreach ($itemPages as $itemPage) {
                $pageComments = $this->dependencies->commentHandling()->getSortedCommentsOfParent($comments, $itemPage->getPageNo());
                $renderContext= [
                    'page' => [
                        'corrector_name' => $summary->getCorrectorName(),
                        'page_no' => $itemPage->getPageNo(),
                        'comments' => $this->dependencies->commentHandling()->getCommentsHtml($pageComments, $this->context->getCorrectionSettings())
                    ]];

                $image = $this->context->getPageImage($itemPage->getKey());
                $path = '';
                if (isset($image)) {
                    $commented = $this->dependencies->image()->applyCommentsMarks($itemPage->getPageNo(), $image, $pageComments);
                    $path = $this->getPageImagePathForPdf($commented);
                }
                $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_content_de.html', $renderContext);

                $pdfSettings = new PdfSettings(false, false, 5,5,5,5);
                $pdfParts[] = $this->getStandardPdfPart([
                    new PdfImage(
                        $path,
                        0,0, 148,210     // A5
                    ),
                    new PdfHtml(
                        $html,
                        150
                    )
                ], $pdfSettings)->withOrientation(PdfPart::ORIENTATION_LANDSCASPE);
             }
        }
        else {
            $essay = $item->getWrittenEssay();
            $renderContext= [
                'text' => [
                'comments' => $this->dependencies->html()->processCommentsForPdf($essay, $this->context->getWritingSettings(), $this->context->getCorrectionSettings(), $comments)
            ]];
            $html = $this->dependencies->html()->fillTemplate(__DIR__ . '/templates/corrector_content_de.html', $renderContext);
            $pdfParts[] =
                $this->getStandardPdfPart([
                    new PdfHtml($html)
                    ]
                )->withPrintFooter(false);
        }
        
        return $pdfParts;
    }
}