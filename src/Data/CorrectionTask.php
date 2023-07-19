<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Data object for a correction task
 */
class CorrectionTask
{
    protected $title;
    protected $instructions;
    protected $solution;
    protected $correction_end;

    /**
     * Constructor (see getters)
     */
    public function __construct(string $title, string $instructions, string $solution, ?int $correction_end)
    {
        $this->title = $title;
        $this->instructions = $instructions;
        $this->solution = $solution;
        $this->correction_end = $correction_end;
    }

    /**
     * Title of the task, to be shown in the app bar
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * Instructions that are shown to the student when the writer opens
     */
    public function getInstructions(): string
    {
        return $this->instructions;
    }

    /**
     * Solution hints that are shown to the student after a defined date
     */
    public function getSolution(): string
    {
        return $this->solution;
    }

    
    /**
     * Unix timestamp for the end of correction
     * If set, no input will be accepted after the end
     */
    public function getCorrectionEnd(): ?int
    {
        return $this->correction_end;
    }

}