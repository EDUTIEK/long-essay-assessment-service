<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Data object for writer comments
 */
class WritingAnnotation
{
    private string $resource_key;
    private string $mark_key;
    private ?string $mark_value;
    private int $parent_number;
    private int $start_position;
    private int $end_position;
    private ?string $comment;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        string $resource_key,
        string $mark_key,
        ?string $mark_value = null,
        int $parent_number = 0,
        int $start_position = 0,
        int $end_position = 0,
        ?string $comment = null
    )
    {
        $this->resource_key = $resource_key;
        $this->mark_key = $mark_key;
        $this->mark_value = $mark_value;
        $this->parent_number = $parent_number;
        $this->start_position = $start_position;
        $this->end_position = $end_position;
        $this->comment = $comment;
    }

    public function getResourceKey(): string
    {
        return $this->resource_key;
    }

    public function getMarkKey(): string
    {
        return $this->mark_key;
    }

    public function getMarkValue(): ?string
    {
        return $this->mark_value;
    }

    public function getParentNumber(): int
    {
        return $this->parent_number;
    }

    public function getStartPosition(): int
    {
        return $this->start_position;
    }

    public function getEndPosition(): int
    {
        return $this->end_position;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}