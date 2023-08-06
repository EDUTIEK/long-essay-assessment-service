<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class DocuItem
{
    private string $key;
    private WritingTask $writingTask;
    private WrittenEssay $writtenEssay;
    /** @var CorrectionSummary[] */
    private array $correctionSummaries = [];
    /** @var correctionComment[] */
    private array $correctionComments = [];

    /**
     * @param string $key
     * @param WritingTask $writingTask
     * @param WrittenEssay $writtenEssay
     * @param CorrectionSummary[] $correctionSummaries
     * @param CorrectionComment[] $correctionComments                                               
     */
    public function __construct(
        string $key,
        WritingTask $writingTask,
        WrittenEssay $writtenEssay,
        array $correctionSummaries,
        array $correctionComments
    ) {
        $this->key = $key;
        $this->writingTask = $writingTask;
        $this->writtenEssay = $writtenEssay;
        $this->correctionSummaries = $correctionSummaries;
        $this->correctionComments = $correctionComments;
    }


    /**
     * The key must correspond to the key of the correction item
     * This will normally be the key of the essay writer
     */
    public function getKey(): string
    {
        return $this->key;
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