<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionPreferences
{
    private string $corrector_key;
    private float $essay_page_zoom;           
    private float $essay_text_zoom ;          
    private float $summary_text_zoom;         
    private int $include_comments;            
    private int $include_comment_ratings;     
    private int $include_comment_points;      
    private int $include_criteria_points;     

    /**
     * Constructor (see getters)
     */
    public function __construct(
        string $corrector_key,
        float $essay_page_zoom = 0.25,           
        float $essay_text_zoom =  1,              
        float $summary_text_zoom =  1,            
        int $include_comments = CorrectionSummary::INCLUDE_INFO,            
        int $include_comment_ratings = CorrectionSummary::INCLUDE_INFO,     
        int $include_comment_points = CorrectionSummary::INCLUDE_INFO,      
        int $include_criteria_points = CorrectionSummary::INCLUDE_INFO
    )
    {
        $this->corrector_key = $corrector_key;
        $this->essay_page_zoom = $essay_page_zoom;
        $this->essay_text_zoom = $essay_text_zoom;
        $this->summary_text_zoom = $summary_text_zoom;
        $this->include_comments = $include_comments;
        $this->include_comment_ratings = $include_comment_ratings;
        $this->include_comment_points = $include_comment_points;
        $this->include_criteria_points = $include_criteria_points;
    }

    /**
     * Get the corrector key
     */
    public function getCorrectorKey(): string
    {
        return $this->corrector_key;
    }

    /**
     * Get the zoom of a pdf page display
     */
    public function getEssayPageZoom(): float
    {
        return $this->essay_page_zoom;
    }

    /**
     * Get the zoom of an essay text display
     */
    public function getEssayTextZoom(): float
    {
        return $this->essay_text_zoom;
    }

    /**
     * Get the zoom of an essay text display
     */
    public function getSummaryTextZoom(): float
    {
        return $this->summary_text_zoom;
    }

    /**
     * Get how to include comments in the authorized correction
     */
    public function getIncludeComments(): int
    {
        return $this->include_comments;
    }

    /**
     * Get hhow to include comment ratings in the authorized correction
     */
    public function getIncludeCommentRatings(): int
    {
        return $this->include_comment_ratings;
    }

    /**
     * Get how to include comment points in the authorized correction
     */
    public function getIncludeCommentPoints(): int
    {
        return $this->include_comment_points;
    }

    /**
     * Get how to include criteria points in the authorized correction
     */
    public function getIncludeCriteriaPoints(): int
    {
        return $this->include_criteria_points;
    }
}