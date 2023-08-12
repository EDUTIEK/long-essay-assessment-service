<?php

namespace Edutiek\LongEssayAssessmentService\Internal\Data;

class PdfHtml extends PdfElement
{
    private $html;

    /**
     * Constructor
     * @param $html - html code of the element
     * @param ...$args - see parent class
     * @see PdfElement
     */
    public function __construct($html, ...$args)
    {
        parent::__construct(...$args);

        $this->html = $html;
    }

    /**
     * Get the html code
     */
    public function getHtml()
    {
        return $this->html;
    }
}