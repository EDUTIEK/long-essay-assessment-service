<?php

namespace Edutiek\LongEssayAssessmentService\Data;

/**
 * Data object for a resource defined in the environment
 * For display in the writer or corrector
 */
class EnvResource
{
    const TYPE_FILE = 'file';
    const TYPE_URL = 'url';

    protected $key;
    protected $title;
    protected $type;
    protected $source;
    protected $mimetype;
    protected $size;

    /**
     * Constructor (see getters)
     */
    public function __construct(string $key, string $title, string $type, string $source, ?string $mimetype = null, ?int $size = null)
    {
        $this->key = $key;
        $this->title = $title;
        $this->type = $type;
        $this->source = $source;
        $this->mimetype = $mimetype;
        $this->size = $size;

        if (urlencode($key) != $key) {
            throw new \InvalidArgumentException("key must not change by urlencode, given: $key");
        }

        if ($type == self::TYPE_FILE && empty($mimetype)) {
            throw new \InvalidArgumentException("mime type must be given for a file resource");
        }

        if ($type == self::TYPE_FILE && empty($size)) {
            throw new \InvalidArgumentException("mime type must be given for a file resource");
        }
    }

    /**
     * Identifying key
     * The key must be unique for a writing task
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Resource title
     * This will be displayed in side navigation of the writer
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Resource type (file or url)
     * URLs are always opened in a new window
     * Files are embedded if the mime type is supported (e.g. PDF or image types)
     * Otherwise files are opened in a new window, too
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get the source
     * If the resource type is a file then the source is the file name
     * If the resource type is a url, then the source is the actual url
     */
    public function getSource() : string
    {
        return $this->source;
    }


    /**
     * Mimetype of a file resource
     * It will determine the way a file resource is embedded on the writer page
     */
    public function getMimetype() : ?string
    {
        return $this->mimetype;
    }

    /**
     * Size of a file resource in bytes
     * It will determine if the resource can be cached in the web app
     */
    public function getSize(): ?int
    {
        return $this->size;
    }
}