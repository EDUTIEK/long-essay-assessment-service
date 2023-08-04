<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionPage
{
    private string $key;
    private string $item_key;
    
    private int $page_no;
    
    private int $width;
    private int $height;
    
    private ?int $thumb_width;
    private ?int $thumb_height;


    public function __construct(
        string $key,
        string $item_key,
        int $page_no,
        int $width,
        int $height,
        ?int $thumb_width = null,
        ?int $thumb_height = null
    )
    {
        $this->key = $key;
        $this->item_key = $item_key;
        $this->page_no = $page_no;
        $this->width = $width;
        $this->height = $height;
        $this->thumb_width = $thumb_width;
        $this->thumb_height = $thumb_height;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getItemKey(): string
    {
        return $this->item_key;
    }

    /**
     * @return int
     */
    public function getPageNo(): int
    {
        return $this->page_no;
    }
    
    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return int|null
     */
    public function getThumbWidth(): ?int
    {
        return $this->thumb_width;
    }

    /**
     * @return int|null
     */
    public function getThumbHeight(): ?int
    {
        return $this->thumb_height;
    }

}