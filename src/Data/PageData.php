<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Page of a PDF from scanned or processed written text used for correction
 * This object only describes some page metadata
 * The actual page content has to be provided as a PageImage object
 * @see PageImage
 */
class PageData
{
    private string $key;
    private int $page_no;
    
    private int $width;
    private int $height;
    
    private ?int $thumb_width;
    private ?int $thumb_height;

    /**
     * Constructor
     * @param string   $key - unique key of the page
     * @param int      $page_no - sequential number of the page in the item (starting with 1)
     * @param int      $width - with of the full page image
     * @param int      $height - height of the full page image
     * @param int|null $thumb_width - with of a page thumbnail
     * @param int|null $thumb_height - height of a page thumbnail
     */
    public function __construct(
        string $key,
        int $page_no,
        int $width,
        int $height,
        ?int $thumb_width = null,
        ?int $thumb_height = null
    )
    {
        $this->key = $key;
        $this->page_no = $page_no;
        $this->width = $width;
        $this->height = $height;
        $this->thumb_width = $thumb_width;
        $this->thumb_height = $thumb_height;
    }

    /**
     * Get the unique key of the page
     */
    public function getKey(): string
    {
        return $this->key;
    }


    /**
     * Get the sequential number of the page in the item (starting with 1)
     * Comments will refer to this number
     */
    public function getPageNo(): int
    {
        return $this->page_no;
    }
    
    /**
     * Get the width of the full page image
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the height of the full page image
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get the with of a page thumbnail
     */
    public function getThumbWidth(): ?int
    {
        return $this->thumb_width;
    }

    /**
     * Get the height of a page thumbnail
     */
    public function getThumbHeight(): ?int
    {
        return $this->thumb_height;
    }

}