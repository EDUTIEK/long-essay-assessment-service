<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Comment created by a corrector
 * The comment either refers to a marked part of the preprocessed text
 * Or it has CorrectionMark objects for graphical marks on a page image
 */
class CorrectionComment
{
    const RATING_CARDINAL = 'cardinal';
    const RAITNG_EXCELLENT = 'excellent';

    protected string $key = '';
    protected string $item_key = '';
    protected string $corrector_key = '';
    protected int $start_position = 0;
    protected int $end_position = 0;
    protected int $parent_number = 0;
    protected string $comment = '';
    protected string $rating = '';
    protected array $marks = [];
    
    // not in constructor
    protected string $label = '';
    protected bool $show_rating = true;
    protected bool $show_points = true;
    protected int $points = 0;

    /**
     * Constructor
     * @param string $key - unique key of the comment
     * @param string $item_key - key of the correction item 
     * @param string $corrector_key - key of the corrector
     * @param int    $start_position - number of the first word from the marked text or the lowest y position of the correction marks
     * @param int    $end_position - number of the last word fom the marked text
     * @param int $parent_number - number of the parent paragraph of the first marked word or the page number of the correction marks
     * @param string $comment - textual comment
     * @param string $rating - rating flag (see constants)
     * @param CorrectionMark[]  $marks - correction marks which are assigned to this comment
     */
    public function __construct(
        string $key,
        string $item_key,
        string $corrector_key,
        int $start_position,
        int $end_position,
        int $parent_number,
        string $comment,
        string $rating,
        array $marks = []
    )
    {
        $this->key = $key;
        $this->item_key = $item_key;
        $this->corrector_key = $corrector_key;
        $this->start_position = $start_position;
        $this->end_position = $end_position;
        $this->parent_number = $parent_number;
        $this->comment = $comment;
        $this->rating = $rating;
        $this->marks = $marks;
    }

    /**
     * Get the unique key of the comment
     * Starts with 'temp' for a not yet saved comment
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the key of the correction item to which the comment belongs
     */
    public function getItemKey(): string
    {
        return $this->item_key;
    }

    /**
     * Get the key of the corrector to which the comment belongs
     */
    public function getCorrectorKey(): string
    {
        return $this->corrector_key;
    }


    /**
     * Get the number of the first word from the marked text to which the comment belongs
     * or the lowest y position of the correction marks
     */
    public function getStartPosition(): int
    {
        return $this->start_position;
    }

    /**
     * Get the number of the last word from the marked text to which the comment belongs
     * or the page number of the correction marks
     */
    public function getEndPosition(): int
    {
        return $this->end_position;
    }

    /**
     * Get the number of the parent paragraph of the first marked word
     */
    public function getParentNumber(): int
    {
        return $this->parent_number;
    }


    /**
     * Get the textual comment
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Get the rating flag (see constants)
     */
    public function getRating(): string
    {
        return $this->rating;
    }


    /**
     * Get the correction marks which are assigned to this comment
     * @return CorrectionMark[]
     */
    public function getMarks(): array
    {
        return $this->marks;
    }


    /**
     * Get the points that are assigned for showing
     */
    public function getPoints(): int
    {
        return $this->points;
    }


    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param int $points
     * @return CorrectionComment
     */
    public function withPoints(int $points): CorrectionComment
    {
        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    /**
     * @param string $label
     * @return CorrectionComment
     */
    public function withLabel(string $label): CorrectionComment
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @return bool
     */
    public function showRating(): bool
    {
        return $this->show_rating;
    }

    /**
     * @param bool $show_rating
     * @return CorrectionComment
     */
    public function withShowRating(bool $show_rating): CorrectionComment
    {
        $clone = clone $this;
        $clone->show_rating = $show_rating;
        return $clone;
    }

    /**
     * @return bool
     */
    public function showPoints(): bool
    {
        return $this->show_points;
    }

    /**
     * @param bool $show_points
     * @return CorrectionComment
     */
    public function withShowPoints(bool $show_points): CorrectionComment
    {
        $clone = clone $this;
        $clone->show_points = $show_points;
        return $clone;
    }

    public function hasDetailsToShow() : bool 
    {
        return !empty($this->getComment())
            || (!empty($this->getRating()) && $this->showPoints())
            || (!empty($this->getPoints()) && $this->showPoints());
    }

}
