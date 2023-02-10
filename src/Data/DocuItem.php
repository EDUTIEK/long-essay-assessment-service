<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class DocuItem
{
    private WritingTask $writingTask;
    private WrittenEssay $writtenEssay;
    /** @var CorrectionSummary[] */
    private array $correctionSummaries = [];

    /**
     * @param WritingTask $writingTask
     * @param WrittenEssay $writtenEssay
     * @param CorrectionSummary[] $correctionSummaries
     */
    public function __construct(
        WritingTask $writingTask,
        WrittenEssay $writtenEssay,
        array $correctionSummaries
    ) {

        $this->writingTask = $writingTask;
        $this->writtenEssay = $writtenEssay;
        $this->correctionSummaries = $correctionSummaries;
    }

    /**
     * @return WritingTask
     */
    public function getWritingTask(): WritingTask
    {
        return $this->writingTask;
    }

    /**
     * @return WrittenEssay
     */
    public function getWrittenEssay(): WrittenEssay
    {
        return $this->writtenEssay;
    }

    /**
     * @return CorrectionSummary[]
     */
    public function getCorrectionSummaries(): array
    {
        return $this->correctionSummaries;
    }


}