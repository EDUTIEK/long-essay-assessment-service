<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionPoints
{
    private string $key;
    private string $comment_key;
    private string $criterion_key;
    private int $points;

    public function __construct(
        string $key,
        string $comment_key,
        string $criterion_key,
        int $points
    ) {
        $this->key = $key;
        $this->comment_key = $comment_key;
        $this->criterion_key = $criterion_key;
        $this->points = $points;
    }

    /**
     * Get the unique key of the points
     * Starts with 'temp' for not yet saved points
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the key of the comment to which the points belong
     */
    public function getCommentKey(): string
    {
        return $this->comment_key;
    }

    /**
     * Get the key of the criterion for which the oints are given
     */
    public function getCriterionKey(): string
    {
        return $this->criterion_key;
    }

    /**
     * Get the given points
     */
    public function getPoints(): int
    {
        return $this->points;
    }
}
