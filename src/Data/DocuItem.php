<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class DocuItem
{
    private WritingTask $writingTask;
    private WrittenEssay $writtenEssay;
    /** @var CorrectionSummary[] */
    private array $correctionSummaries = [];
    /** @var correctionComment[] */
    private array $correctionComments = [];

    /**
     * @param WritingTask $writingTask
     * @param WrittenEssay $writtenEssay
     * @param CorrectionSummary[] $correctionSummaries
     * @param CorrectionComment[] $correctionComments                                               
     */
    public function __construct(
        WritingTask $writingTask,
        WrittenEssay $writtenEssay,
        array $correctionSummaries,
        array $correctionComments
    ) {

        $this->writingTask = $writingTask;
        $this->writtenEssay = $writtenEssay;
        $this->correctionSummaries = $correctionSummaries;
        $this->correctionComments = $correctionComments;
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

    /**
     * @return CorrectionComment[]
     */
    public function getCorrectionComments(): array
    {
        return $this->correctionComments;
    }

    /**
     * @return CorrectionComment[]
     */
    public function getCommentsByCorrectorKey(string $correctorKey): array
    {
        $comments = [];
        foreach ($this->correctionComments as $comment) {
            if ($comment->getCorrectorKey() == $correctorKey) {
                $comments[] = $comment;
            }
        }
        return $comments;
    }
}