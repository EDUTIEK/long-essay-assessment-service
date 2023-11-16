<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionSummary
{
    const INCLUDE_NOT = 0;          // don't conclude to documentation
    const INCLUDE_INFO = 1;         // include to documentation as info
    const INCLUDE_RELEVANT = 2;     // include to documentation as relevant for the result

    /**
     * Levels of including details of a correction in the final documentation
     */
    const DOCU_INCLUDE_LEVELS = [
        self::INCLUDE_NOT,
        self::INCLUDE_INFO,
        self::INCLUDE_RELEVANT
    ];

    private string $item_key;
    private string $corrector_key;
    private ?string $text;
    private ?float $points;
    private ?string $grade_key;
    private ?int $last_change;
    private bool $is_authorized;
    private ?int $include_comments;
    private ?int $include_comment_ratings;
    private ?int $include_comment_points;
    private ?int $include_criteria_points;
    private ?int $include_writer_notes;
    
    private ?string $corrector_name;
    private ?string $grade_title;

    
    public function __construct(
        string $item_key,
        string $corrector_key,
        ?string $text = null,
        ?float $points = null,
        ?string $grade_key = null,
        ?int $last_change = null,
        bool $is_authorized = false,
        ?int $include_comments = null,
        ?int $include_comment_ratings = null,
        ?int $include_comment_points = null,
        ?int $include_criteria_points = null,
        ?int $include_writer_notes = null,

        // for documentation
        ?string $corrector_name = '',
        ?string $grade_title = ''
    )
    {
        $this->item_key = $item_key;
        $this->corrector_key = $corrector_key;
        $this->text = $text;
        $this->points = $points;
        $this->grade_key = $grade_key;
        $this->last_change = $last_change;
        $this->is_authorized = $is_authorized;
        $this->include_comments = $include_comments;
        $this->include_comment_ratings = $include_comment_ratings;
        $this->include_comment_points = $include_comment_points;
        $this->include_criteria_points = $include_criteria_points;
        $this->include_writer_notes = $include_writer_notes;
        
        $this->corrector_name = $corrector_name;
        $this->grade_title = $grade_title;

    }
    
    /**
     * @return string
     */
    public function getItemKey(): string
    {
        return $this->item_key;
    }

    /**
     * @return string
     */
    public function getCorrectorKey(): string
    {
        return $this->corrector_key;
    }

    /**
     * Get the textual summary
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Get the given points
     */
    public function getPoints(): ?float
    {
        return $this->points;
    }

    /**
     * Get the key of the selected grade
     */
    public function getGradeKey(): ?string
    {
        return $this->grade_key;
    }

    /**
     * Get the unix timestamp of the last change
     */
    public function getLastChange(): ?int
    {
        return $this->last_change;
    }

    /**
     * Get the authorization status
     */
    public function isAuthorized(): bool
    {
        return $this->is_authorized;
    }

    /**
     * Get the level of including comments to the documentation
     */
    public function getIncludeComments(): ?int
    {
        return $this->include_comments;
    }

    /**
     * Get the level of including comment ratings to the documentation
     */
    public function getIncludeCommentRatings(): ?int
    {
        return $this->include_comment_ratings;
    }

    /**
     * Get the level of including comment points to the documentation
     */
    public function getIncludeCommentPoints(): ?int
    {
        return $this->include_comment_points;
    }

    /**
     * Get the level of including criteria points to the documentation
     */
    public function getIncludeCriteriaPoints(): ?int
    {
        return $this->include_criteria_points;
    }
    
    /**
     * Get the level of including writer notes to the documentation
     */
    public function getIncludeWriterNotes(): ?int
    {
        return $this->include_writer_notes;
    }

    /**
     * Get the corrector name
     */
    public function getCorrectorName(): ?string
    {
        return $this->corrector_name;
    }

    /**
     * Get the title of the reached grade
     */
    public function getGradeTitle(): ?string
    {
        return $this->grade_title;
    }

    /**
     * 
     * @param int $last_change
     * @return CorrectionSummary
     */
    public function withLastChange(int $last_change): CorrectionSummary
    {
        $clone = clone $this;
        $clone->last_change = $last_change;
        return $clone;
    }
}