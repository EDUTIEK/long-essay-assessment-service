<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\DocuItem;
use Mustache_Engine;
use Edutiek\LongEssayAssessmentService\Data\CorrectionPage;
use ILIAS\Plugin\LongEssayAssessment\Data\Essay\CorrectorComment;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;

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
     */
    public function getCorrectionAsPdf(DocuItem $item) : string
    {
        $task = $item->getWritingTask();
        $essay = $item->getWrittenEssay();
        $processedText = $this->dependencies->html()->processWrittenText((string) $essay->getWrittenText());
        $pages = $this->context->getPagesOfItem($item->getKey());
        
        $context = [
            'writing' => [
                   'edit_started' =>  $this->formatDates($essay->getEditStarted()),
                   'edit_ended' =>  $this->formatDates($essay->getEditEnded()),
                   'writing_excluded' => $this->formatDates($task->getWritingExcluded()),
                   'is_authorized' => $essay->isAuthorized(),
                   'writing_authorized' =>  $this->formatDates($essay->getWritingAuthorized()),
                   'writing_authorized_by' => $essay->getWritingAuthorizedBy(),
                   ],
            'correction' => [
                   'correction_finalized' => $this->formatDates($essay->getCorrectionFinalized()),
                   'correction_finalized_by' => $essay->getCorrectionFinalizedBy(),
                   'final_points' => $essay->getFinalPoints(),
                   'final_grade' => $essay->getFinalGrade(),
                   'stitch_comment' => $essay->getStitchComment()
            ],
            'text' => $this->dependencies->html()->processTextForPdf($processedText),
            'pages' => $this->getOriginalPageImageDataForPdf($pages),
            'summaries' => []
        ];
        
        foreach ($item->getCorrectionSummaries() as $summary) {
            
            $comments = $item->getCommentsByCorrectorKey($summary->getCorrectorKey());
            
            $context['summaries'][] = [
                'corrector_name' =>  $summary->getCorrectorName(),
                'is_authorized' => $summary->isAuthorized(),
                'last_change' => $this->formatDates($summary->getLastChange()),
                'points' => $summary->getPoints(),
                'grade_title' => $summary->getGradeTitle(),
                'text' => $summary->getText(),
                'pages' => $this->getCommentedPageImageDataForPdf($pages, $comments),
                'comments' => $this->dependencies->html()->processCommentsForPdf(
                    $processedText, $comments)
            ];
        }
        
        $mustache = new Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $template = file_get_contents(__DIR__ . '/templates/correction_de.html');
        $allHtml = $mustache->render($template, $context);
        
        return $this->dependencies->pdfGeneration()->generatePdfFromHtml(
            $allHtml,
            $this->context->getSystemName(),
            $task->getWriterName(),
            $task->getTitle(),
            $task->getWriterName() . ' ' . $this->formatDates($essay->getEditStarted(), $essay->getEditEnded())
        );
    }

    /**
     * Get the data of the original writer pages for pdf processing 
     * @param CorrectionPage[] $pages
     * @return array
     */
    protected function getOriginalPageImageDataForPdf(array $pages) : array
    {
        $data = [];
        foreach ($pages as $page) {
            $image = $this->context->getPageImage($page->getKey());
            if (isset($image)) {
                $data[] = [
                    'page_no' => $page->getPageNo(),
                    'src' => $this->dependencies->image()->getImageSrcAsPathForTCPDF(
                        $image, 
                        $this->context->getAbsoluteTempPath(),
                        $this->context->getRelativeTempPath())
                ];
            }
        }
        return $data;
    }

    /**
     * Get the data of the original writer pages for pdf processing
     * @param CorrectionPage[] $pages
     * @param CorrectionComment[] $comments
     * @return array
     */
    protected function getCommentedPageImageDataForPdf(array $pages, array $comments) : array
    {
        $data = [];
        foreach ($pages as $page) {
            $image = $this->context->getPageImage($page->getKey());
            $commented = $this->dependencies->image()->applyCommentsMarks($page, $image, $comments);
            
            if (isset($commented)) {
                $data[] = [
                    'page_no' => $page->getPageNo(),
                    'src' => $this->dependencies->image()->getImageSrcAsPathForTCPDF(
                        $commented,
                        $this->context->getAbsoluteTempPath(),
                        $this->context->getRelativeTempPath()
                    )
                ];
            }
        }
        return $data;
    }
}