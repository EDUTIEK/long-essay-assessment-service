<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class PageImage
{
    /**
     * @var resource
     */
    private $image;

    /**
     * @var resource
     */
    private $thumbnail;
    
    
    private int $width;
    private int $height;
    
    private ?int $thumb_width;
    private ?int $thumb_height;


    public function __construct(
        $image,
        int $width,
        int $height,
        $thumbnail = null,
        ?int $thumb_width = null,
        ?int $thumb_height = null
    )
    {
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
        $this->thumbnail = $thumbnail;
        $this->thumb_width = $thumb_width;
        $this->thumb_height = $thumb_height;
    }
    
    /**
     * @return resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return resource|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
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