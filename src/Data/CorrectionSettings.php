<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionSettings
{
    public function isMutualVisibility() : bool
    {
        return $this->mutual_visibility;
    }
    private $mutual_visibility;
    private $multi_color_highlight;
    private $max_points;
    private float $max_auto_distance;
    private bool $stitch_when_distance;
    private bool $stitch_when_decimals;
    private string $positive_rating;
    private string $negative_rating;
    private string $headline_scheme;
    private bool $fixed_inclusions;
    private int $include_comments;
    private int $include_comment_ratings;
    private int $include_comment_points;
    private int $include_criteria_points;

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
        string $negative_rating,
        string $headline_scheme,
        bool $fixed_inclusions = false,
        int $include_comments = CorrectionSummary::INCLUDE_NOT,
        int $include_comment_ratings = CorrectionSummary::INCLUDE_NOT,
        int $include_comment_points = CorrectionSummary::INCLUDE_NOT,
        int $include_criteria_points = CorrectionSummary::INCLUDE_NOT
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
        $this->headline_scheme = $headline_scheme;
        $this->fixed_inclusions = $fixed_inclusions;
        $this->include_comments = $include_comments;
        $this->include_comment_ratings = $include_comment_ratings;
        $this->include_comment_points = $include_comment_points;
        $this->include_criteria_points = $include_criteria_points;
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
     * Stitch decision is required when getMaxAutoDistance is exceeded
     */
    public function getStitchWhenDistance(): bool
    {
        return $this->stitch_when_distance;
    }

    /**
     * Stitch decision is required when the average points of correctors are not integer
     */
    public function getStitchWhenDecimals(): bool
    {
        return $this->stitch_when_decimals;
    }

    /**
     * Label of a positive rating
     */
    public function getPositiveRating(): string
    {
        return $this->positive_rating;
    }

    /**
     * Label of a negative rating
     */
    public function getNegativeRating(): string
    {
        return $this->negative_rating;
    }

    /**
     * Name of the headline scheme
     * @see WritingSettings::getHeadlineScheme()
     */
    public function getHeadlineScheme() : string
    {
        return $this->headline_scheme;
    }

    /**
     * Inclusion of correction details in the ducumentation is fixed by the task
     */
    public function hasFixedInclusions() : bool
    {
        return $this->fixed_inclusions;
    }

    /**
     * Correcion comments should be included in the documentation
     * @see CorrectionSummary
     */
    public function getIncludeComments() : int
    {
        return $this->include_comments;
    }

    /**
     * Ratings in correction comments should be included in the documentation
     * @see CorrectionSummary
     */
    public function getIncludeCommentRatings() : int
    {
        return $this->include_comment_ratings;
    }

    /**
     * Points in correction comments should be included in the documentation
     * @see CorrectionSummary
     */
    public function getIncludeCommentPoints() : int
    {
        return $this->include_comment_points;
    }

    /**
     * Points given for rating criteria should be included in the documentation
     * @see CorrectionSummary
     */
    public function getIncludeCriteriaPoints() : int
    {
        return $this->include_criteria_points;
    }
}