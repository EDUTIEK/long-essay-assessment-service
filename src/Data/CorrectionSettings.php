<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionSettings
{
    private $mutual_visibility;
    private $multi_color_highlight;
    private $max_points;
    private float $max_auto_distance;
    private bool $stitch_when_distance;
    private bool $stitch_when_decimals;
    private string $positive_rating;
    private string $negative_rating;


    /**
     * Constructor (see getters)
     */
    public function __construct(
        bool $mutual_visibility,
        bool $multi_color_highlight,
        int $max_points,
        float $max_auto_distance,
        bool $stitch_when_distance,
        bool $stitch_when_decimals,
        string $positive_rating,
        string $negative_rating
    )
    {
        $this->mutual_visibility = $mutual_visibility;
        $this->multi_color_highlight = $multi_color_highlight;
        $this->max_points = $max_points;
        $this->max_auto_distance = $max_auto_distance;
        $this->stitch_when_distance = $stitch_when_distance;
        $this->stitch_when_decimals = $stitch_when_decimals;
        $this->positive_rating = $positive_rating;
        $this->negative_rating = $negative_rating;
    }

    /**
     * Correctors see the other's votes in the app
     */
    public function hasMutualVisibility(): bool
    {
        return $this->mutual_visibility;
    }

    /**
     * Text can be highlighted in multicolor
     */
    public function hasMultiColorHighlight() : bool
    {
        return $this->multi_color_highlight;
    }

    /**
     * Maximum Points to be given
     */
    public function getMaxPoints() : int
    {
        return $this->max_points;
    }

    /**
     * Maximum distance of points given by correctors to allow an automated finalisation
     */
    public function getMaxAutoDistance(): float
    {
        return $this->max_auto_distance;
    }

    /**
     * @return bool
     */
    public function getStitchWhenDistance(): bool
    {
        return $this->stitch_when_distance;
    }

    /**
     * @return bool
     */
    public function getStitchWhenDecimals(): bool
    {
        return $this->stitch_when_decimals;
    }

    /**
     * @return string
     */
    public function getPositiveRating(): string
    {
        return $this->positive_rating;
    }

    /**
     * @return string
     */
    public function getNegativeRating(): string
    {
        return $this->negative_rating;
    }
}