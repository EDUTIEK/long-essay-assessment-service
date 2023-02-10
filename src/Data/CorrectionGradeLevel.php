<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionGradeLevel
{
    protected $key;
    protected $title;
    protected $min_points;


    /**
     * Constructor (see getters)
     */
    public function __construct(string $key, string $title, float $min_points)
    {
        $this->key = $key;
        $this->title = $title;
        $this->min_points = $min_points;
    }

    /**
     * Get the key identifying this level
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the text that should be shown for this level
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the minimum points that are needed to reach this level
     */
    public function getMinPoints(): float
    {
        return $this->min_points;
    }


}