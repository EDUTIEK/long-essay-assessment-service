<?php

namespace Edutiek\LongEssayAssessmentService\Internal;


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
     * Main font name (helvetica, times, ...)
     */
    protected $main_font = 'times';

    /**
     * Size of the main text
     */
    protected $main_font_size = 10;

    /**
     * Font used in the header
     */
    protected $header_font = 'helvetica';


    /**
     * Size of the header text
     */
    protected $header_font_size = 12;

    /**
     * Font used in the footer
     */
    protected $footer_font = 'helvetica';

    /**
     * Size of the footer text
     */
    protected $footer_font_size = 10;

    /**
     * Font used fpr monospace text
     */
    protected $mono_font = 'courier';

    /**
     * Header margin.
     */
    protected $header_margin = 5;

    /**
     * Footer margin.
     */
    protected $footer_margin = 10;

    /**
     * Top margin.
     */
    protected $top_margin = 27;

    /**
     * Bottom margin.
     */
    protected $bottom_margin = 25;

    /**
     * Left margin.
     */
    protected $left_margin = 15;

    /**
     * Right margin.
     */
    protected $right_margin = 15;


    /**
     * Generate a pdf from an HTML text
     * Compliance with PDF/A-2B shall be achieved
     * @see https://de.wikipedia.org/wiki/PDF/A
     * @see
     *
     * @param string $html          HTML code of the content
     * @param string $creator       Name of the creator app, e.h. name of the LMS
     * @param string $author
     * @param string $title
     * @param string $subject
     * @param string $keywords
     * @return string
     */
    public function generatePdfFromHtml(string $html, $creator = "", $author = "", $title = "", $subject = "", $keywords = "") : string
    {
        // create new PDF document
        // note the last parameter for compliance with PDF/A-2B
        $pdf = new \TCPDF($this->page_orientation, $this->pdf_unit, $this->page_format, true, 'UTF-8', false, 2);

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

        // set margins
        $pdf->SetMargins($this->left_margin, $this->top_margin, $this->right_margin);
        $pdf->SetHeaderMargin($this->header_margin);
        $pdf->SetFooterMargin($this->footer_margin);

        // set auto page breaks
        $pdf->SetAutoPageBreak(true, $this->bottom_margin);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        $pdf->SetFont($this->main_font, '', $this->main_font_size, '', true);


        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();



        // Print text using writeHTMLCell()
        //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->writeHtml($html, false, true);

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        return $pdf->Output('dummy.pdf', 'S');
    }
}