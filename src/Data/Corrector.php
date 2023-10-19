<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class Corrector
{
    protected string $item_key;
    protected string $corrector_key;
    protected string $title;
    protected string $initials;
    protected int $position;

    /**
     * Constructor (see getters)
     */
    public function __construct(string $item_key, string $corrector_key, string $title, string $initials, int $position)
    {
        $this->item_key = $item_key;
        $this->corrector_key = $corrector_key;
        $this->title = $title;
        $this->initials = $initials;
        $this->position = $position;
    }

    /**
     * Get the key of the correction item to which the corrector is assigned
     */
    public function getItemKey(): string
    {
        return $this->item_key;
    }

    /**
     * Get the corrector key identifying the corrector
     * This must be the user key of the corrector
     */
    public function getCorrectorKey(): string
    {
        return $this->corrector_key;
    }

    /**
     * Get the title that should be displayed for the corrector
     * This should be derived from the user account
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the corrector initials that should be displayed with comments
     * This should be derived from the user account and have max. two characters
     */
    public function getInitials(): string
    {
        return $this->initials;
    }

    /**
     * Get the position of the corrector for the correction item
     * 0 is the first corrector
     */
    public function getPosition(): int
    {
        return $this->position;
    }

}