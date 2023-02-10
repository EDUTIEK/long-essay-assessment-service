<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionItem
{
    protected string $key;
    protected string $title;
    private bool $correction_allowed = false;
    private bool $authorization_allowed = false;


    /**
     * Constructor
     */
    public function __construct(
        string $key,
        string $title,
        bool $correction_allowed = false,
        bool $authorization_allowed = false
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->correction_allowed = $correction_allowed;
        $this->authorization_allowed = $authorization_allowed;
    }

    /**
     * Get the key identifying the correction item
     * This will normally be the key of the essay writer
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the title that should be displayed for that item
     * This may be an anonymous name for the writer
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * @return bool
     */
    public function isCorrectionAllowed(): bool
    {
        return $this->correction_allowed;
    }

    /**
     * @return bool
     */
    public function isAuthorizationAllowed(): bool
    {
        return $this->authorization_allowed;
    }
}