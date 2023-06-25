<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionSummary
{
    private string $item_key;
    private string $corrector_key;
    private ?string $text;
    private ?float $points;
    private ?string $grade_key;
    private ?string $last_change;
    private ?bool $is_authorized;
    private ?string $corrector_name;
    private ?string $grade_title;

    public function __construct(
        string $item_key,
        string $corrector_key,
        ?string $text,
        ?float $points,
        ?string $grade_key,
        ?int $last_change,
        ?bool $is_authorized = false,

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
     * @return string|null
     */
    public function getCorrectorName(): ?string
    {
        return $this->corrector_name;
    }

    /**
     * @return string|null
     */
    public function getGradeTitle(): ?string
    {
        return $this->grade_title;
    }
}