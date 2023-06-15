<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\DocuItem;

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

        $allHtml = '';
        $allHtml .= "<b>Bearbeitung gestartet:</b> " . $this->formatDates($essay->getEditStarted()) . '<br>';
        $allHtml .= "<b>Bearbeitung beeendet:</b> " . $this->formatDates($essay->getEditEnded()) . '<br>';
        if (!empty($task->getWritingExcluded())) {
            $allHtml .= "<b>Von Bearbeitung ausgeschlossen:</b> " . $this->formatDates($task->getWritingExcluded()) . '<br>';
        }
        if ($essay->isAuthorized()) {
            $allHtml .= "<b>Bearbeitung autorisiert:</b> " . $this->formatDates($essay->getWritingAuthorized()). '<br>';
            $allHtml .= "<b>Bearbeitung autorisiert durch:</b> " . $essay->getWritingAuthorizedBy(). '<br>';
        }
        else {
            $allHtml .= "<b>Bearbeitung autorisiert:</b> nicht autorisiert<br>";
        }

        if ($essay->getCorrectionFinalized()) {
            $allHtml .= '<br>';
            $allHtml .= "<b>Korrektur beeendet:</b> " . $this->formatDates($essay->getCorrectionFinalized()) . '<br>';
            $allHtml .= "<b>Korrektur beeendet durch:</b> " . $essay->getCorrectionFinalizedBy() . '<br>';
            $allHtml .= "<b>Finale Punktzahl:</b> " . $essay->getFinalPoints() . '<br>';
            $allHtml .= "<b>Finale Bewertung:</b> " . $essay->getFinalGrade() . '<br>';
            if (!empty($essay->getStitchComment())) {
                $allHtml .= "<b>Stichentscheid mit Begründung:</b><br> " . $essay->getStitchComment() . '<br>';
            }
        }
        else {
            $allHtml .= "<b>Korrektur beeendet:</b> nicht beendet";
        }

        $allHtml .= '<br><b>Abgegebener Text:</b>';
        $allHtml .= '<hr>';
        $allHtml .= $this->dependencies->html()->processWrittenText((string) $essay->getWrittenText());

        foreach ($item->getCorrectionSummaries() as $summary) {
            $allHtml .= '<hr><p></p>';
            $allHtml .= "<b>Korrektor:</b> " . $summary->getCorrectorName() . '<br>';
            if ($summary->isAuthorized()) {
                $allHtml .= "<b>Korrigiert:</b> " . $this->formatDates($summary->getLastChange()) . '<br>';
                $allHtml .= "<b>Vergebene Punkte:</b> " . $summary->getPoints() . '<br>';
                $allHtml .= "<b>Bewertung:</b> " . $summary->getGradeTitle() . '<br>';
                if (!empty($summary->getText())) {
                    $allHtml .= "<b>Kommentar:</b>" . $summary->getText();
                }
                else {
                    $allHtml .= "<b>Kommentar:</b> ohne Kommentar";
                }
            }
            else {
                $allHtml .= '<b>Korrektur:</b> noch nicht abgeschlossen<br>';
            } 
        };

        return $this->dependencies->pdfGeneration()->generatePdfFromHtml(
            $allHtml,
            $this->context->getSystemName(),
            $task->getWriterName(),
            $task->getTitle(),
            $task->getWriterName() . ' ' . $this->formatDates($essay->getEditStarted(), $essay->getEditEnded())
        );
    }
}