<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class WritingPreferences
{
    private float $instructions_zoom;
    private float $editor_zoom;
    private bool $word_count_enabled;
    private bool $word_count_characters;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        float $instructions_zoom = 0.25,           
        float $editor_zoom =  1,
        bool $word_count_enabled = false,
        bool $word_count_characters = false
    )
    {
        $this->instructions_zoom = $instructions_zoom;
        $this->editor_zoom = $editor_zoom;
        $this->word_count_enabled = $word_count_enabled;
        $this->word_count_characters = $word_count_characters;
    }
    
    /**
     * @return float
     */
    public function getInstructionsZoom(): float
    {
        return $this->instructions_zoom;
    }

    public function getEditorZoom(): float
    {
        return $this->editor_zoom;
    }

    public function getWordCountEnabled(): bool
    {
        return $this->word_count_enabled;
    }

    public function getWordCountCharacters(): bool
    {
        return $this->word_count_characters;
    }

}