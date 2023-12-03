<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class WritingPreferences
{
    private float $instructions_zoom;
    private float $editor_zoom;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        float $instructions_zoom = 0.25,           
        float $editor_zoom =  1            
    )
    {
        $this->instructions_zoom = $instructions_zoom;
        $this->editor_zoom = $editor_zoom;
    }
    
    /**
     * @return float
     */
    public function getInstructionsZoom(): float
    {
        return $this->instructions_zoom;
    }

    /**
     * @return float
     */
    public function getEditorZoom(): float
    {
        return $this->editor_zoom;
    }
}