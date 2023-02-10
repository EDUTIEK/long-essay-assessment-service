<?php

namespace Edutiek\LongEssayAssessmentService\Internal;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class HtmlProcessing
{
    static $counter = 1;

    /**
     * Process the written text
     */
    public function processWrittenText(?string $html) : string
    {
        $html = $html ?? '';
        $html = $this->processXslt($html, __DIR__ . '/xsl/cleanup.xsl');
        $html = $this->processXslt($html, __DIR__ . '/xsl/numbers_pdf.xsl');

        return $html;
    }


    /**
     * Get the XSLt Processor for an XSL file
     * @param string $html
     * @param string $xslt_file
     * @return string
     */
    protected function processXslt(string $html, string $xslt_file) : string
    {
        try {
            // get the xslt document
            // set the URI to allow document() within the XSL file
            $xslt_doc = new \DOMDocument('1.0', 'UTF-8');
            $xslt_doc->loadXML(file_get_contents($xslt_file));
            $xslt_doc->documentURI = $xslt_file;

            // get the xslt processor
            $xslt = new \XSLTProcessor();
            $xslt->registerPhpFunctions();
            $xslt->importStyleSheet($xslt_doc);

            // get the html document
            $dom_doc = new \DOMDocument('1.0', 'UTF-8');
            $dom_doc->loadHTML('<?xml encoding="UTF-8"?'.'>'. $html);

            $xml = $xslt->transformToXml($dom_doc);
            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);

            return $xml;
        }
        catch (\Throwable $e) {
            return 'HTML PROCESSING ERROR:<br>' . $e->getMessage() . '<hr>' . $html;
        }
    }


    static function initCounter(): void
    {
        self::$counter = 1;
    }

    static function nextCounter(): string
    {
        return self::$counter++;
    }
}