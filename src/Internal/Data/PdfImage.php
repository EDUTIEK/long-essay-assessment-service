<?php

namespace Edutiek\LongEssayAssessmentService\Internal\Data;

class PdfImage extends PdfElement
{
    private $path;

    /**
     * Constructor
     * @param $path - path of the image file (relative to the executing script)
     * @param ...$args - see parent class
     * @see PdfElement
     */
    public function __construct($path, ...$args) 
    {
        parent::__construct(...$args);

        $this->path = $path;
    }

    /**
     * Get the path of the image file, relative to the executing script
     */
    public function getPath()
    {
        return $this->path;
    }
}