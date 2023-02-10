<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class Corrector
{

    protected $key;
    protected $title;

    /**
     * Constructor (see getters)
     */
    public function __construct(string $key, string $title)
    {
        $this->key = $key;
        $this->title = $title;
    }

    /**
     * Get the corrector key identifying the corrector
     * This must be the user key of the corrector
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the title that should be displayed for the corrector
     * This may be generated name from the user account
     */
    public function getTitle(): string
    {
        return $this->title;
    }

}