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


    private string $mime;
    private int $width;
    private int $height;

    private ?string $thumb_mime;
    private ?int $thumb_width;
    private ?int $thumb_height;

    public function __construct(
        $image,
        string $mime,
        int $width,
        int $height,
        $thumbnail = null,
        ?string $thumb_mime = null,
        ?int $thumb_width = null,
        ?int $thumb_height = null
    )
    {
        $this->image = $image;
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
        $this->thumbnail = $thumbnail;
        $this->thumb_mime = $thumb_mime;
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
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
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
     * @return string|null
     */
    public function getThumbMime(): ?string
    {
        return $this->thumb_mime;
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