<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionRatingCriterion
{
    protected string $key;
    protected string $corrector_key;
    protected string $title;
    protected string $description;
    protected string $points;


    /**
     * Constructor (see getters)
     */
    public function __construct(string $key, string $corrector_key, string $title, string $description, int $points)
    {
        $this->key = $key;
        $this->corrector_key = $corrector_key;
        $this->title = $title;
        $this->description = $description;
        $this->points = $points;
    }

    /**
     * Get the identifying  key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the key of the corrector to which the criterion belongs
     * Depending on the environment setting rating criteria can be fixed or individually defined by correctors
     * - If fixed, the corrector key is always an empty string and the criterion is used for all correctors
     * - If individually, the corrector key is always set and a criterion is only used by this corrector
     */
    public function getCorrectorKey(): string
    {
        return $this->corrector_key;
    }

    /**
     * Get the title (single line)
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the (multi line) description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the points that can be assigned to the criterion
     */
    public function getPoints(): float
    {
        return $this->points;
    }
}
