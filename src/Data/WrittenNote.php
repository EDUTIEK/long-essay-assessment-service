<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class WrittenNote
{
    private int $note_no;
    private ?string $note_text;
    private ?int $last_change;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        int $note_no,
        ?string $note_text,
        ?int $last_change
    ) {
        $this->note_no = $note_no;
        $this->note_text = $note_text;
        $this->last_change = $last_change;
    }

    /**
     * Get the number of the notice board (0 to x)
     */
    public function getNoteNo(): int
    {
        return $this->note_no;
    }

    /**
     * Get the text of the note
     */
    public function getNoteText(): ?string
    {
        return $this->note_text;
    }

    /**
     * Get the time of the last change
     */
    public function getLastChange(): ?int
    {
        return $this->last_change;
    }
}