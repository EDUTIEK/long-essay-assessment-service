<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Data object for writer comments
 */
class CorrectionSnippet
{
    private string $key;
    private string $purpose;
    private ?string $text;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        string $key,
        string $purpose,
        ?string $text = null
    )
    {
        $this->key = $key;
        $this->purpose = $purpose;
        $this->text = $text;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

}