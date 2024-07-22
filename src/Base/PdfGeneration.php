<?php

namespace Edutiek\LongEssayAssessmentService\Base;

use Edutiek\LongEssayAssessmentService\Data;
use Edutiek\LongEssayAssessmentService\Internal\Tcpdf;

class PdfGeneration
{
    /**
     * Page orientation (P=portrait, L=landscape).
     */
    protected $page_orientation = 'P';

    /**
     * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
     */
    protected  $pdf_unit = 'mm';

    /**
     * Page format.
     */
    protected $page_format = 'A4';


    /**
     * Main text of the page
     */
    protected $main_font = 'times';
    protected $main_font_size = 10;

    protected $header_font = 'helvetica';
    protected $header_font_size = 12;

    protected $footer_font = 'helvetica';
    protected $footer_font_size = 10;

    protected $mono_font = 'courier';

    /**
     * Generate a pdf from an HTML text
     * Compliance with PDF/A-2B shall be achieved
     * @see https://de.wikipedia.org/wiki/PDF/A
     *
     * @param Data\PdfPart[] $parts      Parts of the PDF
     * @param string $creator       Name of the creator app, e.h. name of the LMS
     * @param string $author        Name of the author, e.g. user creating the PDF
     * @param string $title         will be shown bold as first line in header
     * @param string $subject       will be shown as second line in header
     * @param string $keywords
     * @return string
     */
    public function generatePdf(array $parts, $creator = "", $author = "", $title = "", $subject = "", $keywords = "") : string
    {
        // create new PDF document
        // note the last parameter for compliance with PDF/A-2B
        $pdf = new Tcpdf($this->page_orientation, $this->pdf_unit, $this->page_format, true, 'UTF-8', false, 2);

        $pdf->setAllowLocalFiles(true);
        
        // set document information
        $pdf->SetCreator($creator);
        $pdf->SetAuthor($author);
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);
        $pdf->SetKeywords($keywords);

        $pdf->SetAlpha(1);

        // set default header data
        $pdf->SetHeaderData('', 0, $title, $subject);   

        // set header and footer fonts
        $pdf->setHeaderFont(Array($this->header_font, '', $this->header_font_size));
        $pdf->setFooterFont(Array($this->footer_font, '', $this->footer_font_size));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont($this->mono_font);

        // Set font
        $pdf->SetFont($this->main_font, '', $this->main_font_size, '', true);


        $pdf->setDisplayMode('fullpage', 'SinglePage', 'UseThumbs');
            
        foreach ($parts as $part) 
        {
            $pdf->SetMargins($part->getLeftMargin(), $part->getTopMargin(), $part->getRightMargin(), true);
            $pdf->setPrintHeader($part->getPrintHeader());
            $pdf->setHeaderMargin($part->getHeaderMargin());
            $pdf->setPrintFooter($part->getPrintFooter());
            $pdf->setFooterMargin($part->getFooterMargin());

            $pdf->AddPage($part->getOrientation(), $part->getFormat(), true);
            
            foreach ($part->getElements() as $element) 
            {
                if ($element instanceof Data\PdfHtml) {
                    $pdf->SetAutoPageBreak(true, $part->getBottomMargin());
                    $pdf->writeHtmlCell(
                        (float) $element->getWidth(),
                        (float) $element->getHeight(),
                        $element->getLeft(),
                        $element->getTop(),
                        $element->getHtml(),
                        0,      // border
                        0,      // ln
                        false,  // fill
                        true,   // reseth
                        '',     // align
                        true    // autopadding
                    );
                }
                elseif ($element instanceof Data\PdfImage) {

                    $pdf->SetAutoPageBreak(false);
                    $pdf->Image(
                        $element->getPath(),
                        (float) $element->getLeft(),
                        (float) $element->getTop(),
                        (float) $element->getWidth(),
                        (float) $element->getHeight(),
                        '', 
                        '', 
                        '', 
                        true, 
                        300, 
                        '', 
                        false, 
                        false, 
                        0, 
                        false,       
                        false, 
                        false, 
                        false,
                        array()
                    );
                }
            }
            
            // important to do this here to avoid an overlapping with next part if html content is longer than a page
            $pdf->lastPage();
        }

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        return $pdf->Output('dummy.pdf', 'S');
    }
}