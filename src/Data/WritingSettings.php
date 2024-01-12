<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class WritingSettings
{
    const HEADLINE_SCHEME_NONE = 'none';
    const HEADLINE_SCHEME_NUMERIC = 'numeric';
    const HEADLINE_SCHEME_EDUTIEK = 'edutiek';

    const FORMATTING_OPTIONS_NONE = 'none';
    const FORMATTING_OPTIONS_MINIMAL = 'minimal';
    const FORMATTING_OPTIONS_MEDIUM = 'medium';
    const FORMATTING_OPTIONS_FULL = 'full';

    private string $headline_scheme;
    private string $formatting_options;
    private int $notice_boards;
    private bool $copy_allowed;
    private string $primary_color;
    private string $primary_text_color;

    private bool $add_paragraph_numbers;
    private int $top_margin;
    private int $bottom_margin;
    private int $left_margin;
    private int $right_margin;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        string $headline_scheme,
        string $formatting_options,
        int $notice_boards,
        bool $copy_allowed,
        string $primary_color,
        string $primary_text_color,
        bool $add_paragraph_numbers,
        int $top_margin,
        int $bottom_margin,
        int $left_margin,
        int $right_margin
    )
    {
        switch ($headline_scheme) {
            case self::HEADLINE_SCHEME_NONE:
            case self::HEADLINE_SCHEME_NUMERIC:
            case self::HEADLINE_SCHEME_EDUTIEK:
                $this->headline_scheme = $headline_scheme;
                break;
            default:
                throw new \InvalidArgumentException("unknown headline scheme: $headline_scheme");
        }

        switch ($formatting_options) {
            case self::FORMATTING_OPTIONS_NONE:
            case self::FORMATTING_OPTIONS_MINIMAL:
            case self::FORMATTING_OPTIONS_MEDIUM:
            case self::FORMATTING_OPTIONS_FULL:
                $this->formatting_options = $formatting_options;
                break;
            default:
                throw new \InvalidArgumentException("unknown formatting options: $headline_scheme");
        }

        if ($notice_boards < 0 and $notice_boards > 5) {
            throw new \InvalidArgumentException("notice boards mut be between 0 and 5, given: $notice_boards");
        }
        else {
            $this->notice_boards = $notice_boards;
        }

        $this->copy_allowed = $copy_allowed;
        $this->primary_color = $primary_color;
        $this->primary_text_color = $primary_text_color;
        $this->add_paragraph_numbers = $add_paragraph_numbers;
        $this->top_margin = $top_margin;
        $this->bottom_margin = $bottom_margin;
        $this->left_margin = $left_margin;
        $this->right_margin = $right_margin;
    }

    /**
     * Get the identifier of the headline scheme
     * none: no formatting
     * numeric: 1 ‧ 1.1 ‧ 1.1.1
     * edudiek: A. ‧ I. ‧ 1 ‧ a. ‧ aa. ‧ (1)
     */
    public function getHeadlineScheme() : string
    {
        return $this->headline_scheme;
    }

    /**
     * Get the identifier of the formatting options
     * none: no formatting
     * minimal: bold, italic underline
     * medium: bold, italic, underline, lists
     * full: bold, italic, underline, lists, headlines
     */
    public function getFormattingOptions() : string
    {
        return $this->formatting_options;
    }

    /**
     * Get the number of the notice boards
     * zero to five
     */
    public function getNoticeBoards() : int
    {
        return $this->notice_boards;
    }

    /**
     * Get if copying from external sites is allowed
     */
    public function isCopyAllowed() : bool
    {
        return $this->copy_allowed;
    }

    /**
     * Get the color for the background of primary actions
     */
    public function getPrimaryColor(): string
    {
        return $this->primary_color;
    }

    /**
     * Get the color for the text of primary actions
     */
    public function getPrimaryTextColor(): string
    {
        return $this->primary_text_color;
    }

    public function getAddParagraphNumbers() : bool
    {
        return $this->add_paragraph_numbers;
    }

    public function getTopMargin() : int
    {
        return $this->top_margin;
    }

    public function getBottomMargin() : int
    {
        return $this->bottom_margin;
    }

    public function getLeftMargin() : int
    {
        return $this->left_margin;
    }

    public function getRightMargin() : int
    {
        return $this->right_margin;
    }
}