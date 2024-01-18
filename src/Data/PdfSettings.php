<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class PdfSettings
{
    /**
     * Minimum margin on all sides of the pdf (mm)
     */
    const MIN_MARGIN = 0;

    /**
     * Height of a header (mm)
     */
    const HEADER_HEIGTH = 15;

    /**
     * Height of a footer (mm)
     */
    const FOOTER_HEIGHT = 5;

    private bool $add_header;
    private bool $add_footer;
    private int $top_margin;
    private int $bottom_margin;
    private int $left_margin;
    private int $right_margin;

    /**
     * Constructor (see getters)
     */
    public function __construct(
        bool $add_header,
        bool $add_footer,
        int $top_margin,
        int $bottom_margin,
        int $left_margin,
        int $right_margin
    )
    {
        $this->add_header = $add_header;
        $this->add_footer = $add_footer;
        $this->top_margin = $top_margin;
        $this->bottom_margin = $bottom_margin;
        $this->left_margin = $left_margin;
        $this->right_margin = $right_margin;
    }

    public function getAddHeader() : bool
    {
        return $this->add_header;
    }

    public function getAddFooter() : bool
    {
        return $this->add_footer;
    }

    public function getTopMargin() : int
    {
        return max($this->top_margin, self::MIN_MARGIN);
    }

    public function getBottomMargin() : int
    {
        return max($this->bottom_margin, self::MIN_MARGIN);
    }

    public function getLeftMargin() : int
    {
        return max($this->left_margin, self::MIN_MARGIN);
    }

    public function getRightMargin() : int
    {
        return max($this->right_margin, self::MIN_MARGIN);
    }

    /**
     * Get the margin of the header, if included
     */
    public function getHeaderMargin() : int
    {
        return $this->getAddHeader() ? $this->getTopMargin() : 0;
    }

    /**
     * Get the margin of the footer, if included
     */
    public function getFooterMargin()  : int
    {
        return $this->getAddFooter() ? $this->getBottomMargin() : 0;
    }

    /**
     * Get the top margin of the content, respecting the header
     */
    public function getContentTopMargin() : int
    {
        return $this->getTopMargin() + ($this->getAddHeader() ? self::HEADER_HEIGTH : 0);
    }

    /**
     * Get the bottom margin of the content, respecting the footer
     */
    public function getContentBottomMargin() : int
    {
        return $this->getBottomMargin() + ($this->getAddFooter() ? self::FOOTER_HEIGHT : 0);
    }

}