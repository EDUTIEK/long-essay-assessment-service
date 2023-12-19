<?php

namespace Edutiek\LongEssayAssessmentService\Internal;

use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSettings;

class CommentHandling
{
    const BACKGROUND_NORMAL = '#D8E5F4';
    const BACKGROUND_EXCELLENT = '#E3EFDD';
    const BACKGROUND_CARDINAL = '#FBDED1';
    
    const FILL_NORMAL = '#3365ff40';
    const FILL_EXCELLENT = '#19e62e40';
    const FILL_CARDINAL = '#bc471040';
    
    const BORDER_NORMAL = '#3365ff';
    const BORDER_EXCELLENT = '#19e62e';
    const BORDER_CARDINAL = '#bc4710';
    
    /**
     * Get the sorted and labelled comments of a parent (page or paragraph)
     * @param CorrectionComment[] $comments
     * @return CorrectionComment[]
     */
    public function getSortedCommentsOfParent(array $comments, int $parent_no) : array
    {
        $sort = [];
        foreach($comments as $comment) {
            if ($comment->getParentNumber() == $parent_no) {
                $key = sprintf('%06d', $comment->getStartPosition()) . $comment->getKey();
                $sort[$key] = $comment;
            }
        }
        ksort($sort);

        $result = [];
        $number = 1;
        foreach ($sort as $comment) {
            // only comments with details to show should get a label
            // others are only marks in the text
            if ($comment->hasDetailsToShow()) {
                $result[] = $comment->withLabel($parent_no . '.' . $number++);
            }
            else {
                $result[] = $comment;
            }
        }

        return $result;
    }

    /**
     * Get html formatted comments for side display in the pdf
     * @param CorrectionComment[] $comments
     */
    public function getCommentsHtml(array $comments, CorrectionSettings $settings) : string
    {
        $html = '';
        foreach ($comments as $comment) {
            if ($comment->hasDetailsToShow()) {
                $content = $comment->getLabel();
                if ($comment->showRating())
                
                $content = $comment->getLabel();
                if ($comment->showRating() && $comment->getRating() == CorrectionComment::RATING_CARDINAL) {
                    $content .= ' ' . $settings->getNegativeRating();
                }
                if ($comment->showRating() && $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT) {
                    $content .= ' ' . $settings->getPositiveRating();
                }

                $color = $this->getTextBackgroundColor([$comment]);
                $content = '<strong style="background-color:'. $color . ';">' . $content . '</strong>';
                
                if (!empty($comment->getComment())) {
                    $content .= ' ' . $comment->getComment();
                }
                
                if ($comment->showPoints() && $comment->getPoints() == 1) {
                    $content .= '<br />(1 Punkt)';
                }
                elseif ($comment->showPoints() && $comment->getPoints() != 0) {
                    $content .= '<br />(' . $comment->getPoints() . ' Punkte)';
                }
                
                $content = '<p>' . $content . '</p>';
                
                $html .= $content . "\n";   
            }
        }
        return $html;
    }
    
    
    /**
     * Get the text background color of a list of overlapping comments
     * Cardinal failures and excellent passages should have precedence
     * @param CorrectionComment[] $comments
     */
    public function getTextBackgroundColor(array $comments) : string
    {
        $color = '';
        foreach ($comments as $comment) {
            if ($comment->showRating() && $comment->getRating() == CorrectionComment::RATING_CARDINAL) {
                $color = self::BACKGROUND_CARDINAL;
            }
            elseif ($comment->showRating() && $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT) {
                $color = self::BACKGROUND_EXCELLENT;
            }
            else if ($color == '') {
                $color = self::BACKGROUND_NORMAL;
            }
        }
        return $color;
    }


    /**
     * Get the fill color for a graphical mark 
     */
    public function getMarkFillColor(CorrectionComment $comment) : string
    {
        if ($comment->showRating() && $comment->getRating() == CorrectionComment::RATING_CARDINAL) {
            return self::FILL_CARDINAL;
        }
        elseif ($comment->showRating() && $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT) {
            return self::FILL_EXCELLENT;
        }
        else {
            return self::FILL_NORMAL;
        }
    }

    /**
     * Get the border color for a graphical mark
     */
    public function getMarkBorderColor(CorrectionComment $comment) : string
    {
        if ($comment->showRating() && $comment->getRating() == CorrectionComment::RATING_CARDINAL) {
            return self::BORDER_CARDINAL;
        }
        elseif ($comment->showRating() && $comment->getRating() == CorrectionComment::RAITNG_EXCELLENT) {
            return self::BORDER_EXCELLENT;
        }
        else {
            return self::BORDER_NORMAL;
        }
    }
}