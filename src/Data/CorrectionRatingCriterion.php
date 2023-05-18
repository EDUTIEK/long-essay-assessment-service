<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionRatingCriterion
{
    protected $key;
    protected $title;
    protected $description;
    protected $points;


    /**
     * Constructor (see getters)
     */
    public function __construct(string $key, string $title, string $description, int $points)
    {
        $this->key = $key;
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
