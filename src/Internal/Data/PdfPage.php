<?php

namespace Edutiek\LongEssayAssessmentService\Internal\Data;

/**
 * Page that has to be added to the Pdf
 * Note: if the page contains long elements then it may create over several real pages
 */
class PdfPage
{
    const ORIENTATION_PORTRAIT = 'portait';
    const ORIENTATION_LANDSCASPE = 'landscape';

    const FORMAT_A4 = 'A4';
    const FORMAT_A5 = 'A5';
    
    private string $format;
    private string $orientation;

    private array $elements;

    /**
     * Constructor
     * @param string $format
     * @param string $orientation
     * @param PdfElement[]  $elements
     */
    public function __construct(
        string $format = self::FORMAT_A4, 
        string $orientation = self::ORIENTATION_PORTRAIT,
        array $elements = []
    ) 
    {
        $this->format = $format;
        $this->orientation = $orientation;
        $this->elements = $elements;
    }

    /**
     * Get the page format
     * @see FORMAT_A4, FORMAT_A5
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Get the page orientation
     * @see ORIENTATION_PORTRAIT, ORIENTATION_LANDSCASPE
     */
    public function getOrientation(): string
    {
        return $this->orientation;
    }

    /**
     * Add an element to the Page
     * @param PdfElement $element
     * @return void
     */
    public function addElement(PdfElement $element) :void
    {
        $this->elements[] = $element;
    }

    /**
     * Get the elements of the page
     * @return PdfElement[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}