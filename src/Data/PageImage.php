<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Page in a PDF file converted to an image
 * The image and its thumbnail are provided as file resources
 */
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

    /**
     * Contructor
     * @param resource    $image - file resource of the image (handler provided by the fopen function)
     * @param string      $mime - mime type of the image
     * @param int         $width - width of the image
     * @param int         $height - height of the image
     * @param             $thumbnail - file resource of a thumbnail (handler provided by the fopen function)
     * @param string|null $thumb_mime - mime type of the thumbnail
     * @param int|null    $thumb_width - width of the thumbnail
     * @param int|null    $thumb_height - height of the thumbnail
     */
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
     * Get the file resource of the image (handler provided by the fopen function)
     * @return resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * get the file resource of a thumbnail (handler provided by the fopen function)
     * @return resource|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }


    /**
     * Get the mime type of the image
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * Get the width of the image
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the height of the image
     */
    public function getHeight(): int
    {
        return $this->height;
    }


    /**
     * Get the mime type of the thumbnail
     */
    public function getThumbMime(): ?string
    {
        return $this->thumb_mime;
    }


    /**
     * Get the width of the thumbnail
     */
    public function getThumbWidth(): ?int
    {
        return $this->thumb_width;
    }

    /**
     * Get the height of the thumbnail
     */
    public function getThumbHeight(): ?int
    {
        return $this->thumb_height;
    }
}