<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Data object for writing steps
 */
class WritingStep
{
    protected $timestamp;
    protected $content;
    protected $is_delta;
    protected $hash_before;
    protected $hash_after;

    /**
     * Constructor (see getters)
     */
    public function __construct(int $timestamp, string $content, bool $is_delta, string $hash_before, string $hash_after)
    {
        $this->timestamp = $timestamp;
        $this->content = $content;
        $this->is_delta = $is_delta;
        $this->hash_before = $hash_before;
        $this->hash_after = $hash_after;
    }

    /**
     * Unix timestamp of the editing step
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Content of the wrtiting step (may be full text or patch text)
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Is the content a patch text
     */
    public function isDelta(): bool
    {
        return $this->is_delta;
    }

    /**
     * Hash value of the full text before applying the step
     */
    public function getHashBefore(): string
    {
        return $this->hash_before;
    }

    /**
     * Hah value of the full text after applying this step
     */
    public function getHashAfter(): string
    {
        return $this->hash_after;
    }


}