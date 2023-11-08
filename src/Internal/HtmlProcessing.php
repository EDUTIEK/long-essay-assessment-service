<?php

namespace Edutiek\LongEssayAssessmentService\Internal;

use DOMDocument;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use ILIAS\Plugin\LongEssayAssessment\Data\Essay\CorrectorComment;
use Mustache_Engine;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class HtmlProcessing
{
    const COLOR_NORMAL = '#D8E5F4';
    const COLOR_EXCELLENT = '#E3EFDD';
    const COLOR_CARDINAL = '#FBDED1';


    static int $paraCounter = 0;
    static int $wordCounter = 0;
    
    /**
     * All Comments that should be merged
     * @var CorrectionComment[]
     */
    static array $allComments = [];

    /**
     * Comments for the current paragraph
     * @var CorrectionComment[]
     */
    static array $currentComments = [];

    
    /**
     * Fill a template with data
     * @param string $template
     * @param array  $data
     * @return string
     */
    public function fillTemplate(string $template, array $data)
    {
        $mustache = new Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $template = file_get_contents($template);
        return $mustache->render($template, $data);
    }
    
    /**
     * Process the written text for usage in the correction
     * This will add the paragraph numbers and split up all text to single word embedded in <w-p> elements.
     *      the 'w' attribute is the word number
     *      the 'p' attribute is the paragraph number
     */
    public function processWrittenText(?string $html) : string
    {
        self::$paraCounter = 0;
        self::$wordCounter = 0;
        
        
        $html = $html ?? '';
        $html = $this->processXslt($html, __DIR__ . '/xsl/cleanup.xsl');
        $html = $this->processXslt($html, __DIR__ . '/xsl/numbers.xsl');

        return $html;
    }

    /**
     * Process the text for inclusion in a pdf without comments
     */
    public function processTextForPdf(?string $html) : string
    {
        $html = $html ?? '';
        $html = preg_replace('/<w-p w="([0-9]+)" p="([0-9]+)">/','', $html);
        $html = str_replace('</w-p>','', $html);

        $html = $this->processXslt($html, __DIR__ . '/xsl/pdf_text.xsl');
        return $html;
    }


    /**
     * Process the text for inclusion in a pdf with comments at hte side comments
     * The text must have been processed with processedWrittenText()
     * @param string|null $html
     * @param CorrectionComment[]  $comments
     * @return string
     */
    public function processCommentsForPdf(?string $html, array $comments) : string
    {
        self::$allComments = $comments;
        self::$currentComments = [];
        
        $html = $html ?? '';
        
        $html = preg_replace('/<w-p w="([0-9]+)" p="([0-9]+)">/','<span data-w="$1" data-p="$2">', $html);
        $html = str_replace('</w-p>','</span>', $html);
        $html = $this->processXslt($html, __DIR__ . '/xsl/pdf_comments.xsl');

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

            //$xml = $xslt->transformToXml($dom_doc);
            $result = $xslt->transformToDoc($dom_doc);
            $xml= $result->saveHTML();
            
            $xml = preg_replace('/<\?xml.*\?>/', '', $xml);
            $xml = str_replace( ' xmlns:php="http://php.net/xsl"', '', $xml);

            return $xml;
        }
        catch (\Throwable $e) {
            return 'HTML PROCESSING ERROR:<br>' . $e->getMessage() . '<hr>' . $html;
        }
    }


    static function initParaCounter(): void
    {
        self::$paraCounter = 0;
    }

    static function currentParaCounter(): string
    {
        return self::$paraCounter;
    }

    static function nextParaCounter(): string
    {
        self::$paraCounter++;
        return self::$paraCounter;
    }

    static function initWordCounter(): void
    {
        self::$wordCounter = 0;
    }

    static function currentWordCounter(): string
    {
        return self::$wordCounter;
    }

    static function nextWordCounter(): string
    {
        self::$wordCounter++;
        return self::$wordCounter;
    }

    /**
     * Split a text into single words
     * Single spaces are added to the last word
     * Multiple spaces are treated as separate words
     * @param $text
     * @return \DOMElement
     * @throws \DOMException
     */
    static function splitWords($text): \DOMElement
    {
       $words = preg_split("/([\s]+)/", $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

       $doc = new DOMDocument;
       $root = $doc->createElement("root");

       $current = '';
       foreach ($words as $word) {
           if ($word == ' ' && (trim($current) == $current || trim($current) == '')) {
               // append a space to the last word if it is pure text or pure space
               // (don't add if a text space is already added to the last word)
               $current .= $word;
           }
           else {
               if ($current != '') {
                   $root->appendChild(new \DOMText($current));
               }
               $current = $word;
           }
       }
        if ($current != '') {
            $root->appendChild(new \DOMText($current));
        }

        return $root;
    }

    /**
     * Initialize the collection of comments for the current paragraph
     */
    static function initCurrentComments(string $paraNumber) 
    {
        $commentHandling = Dependencies::getInstance()->commentHandling();
        self::$currentComments = $commentHandling->getSortedCommentsOfParent(self::$allComments, (int) $paraNumber);
    }

    /**
     * Get a label if a comment starts at the given word
     */
    static function commentLabel(string $wordNumber) : string
    {
        $labels = [];
        foreach(self::$currentComments as $comment) {
            if ((int) $wordNumber == $comment->getStartPosition() && !empty($comment->getLabel())) {
                $labels[] = $comment->getLabel();
            }
        }
        return(implode(',', $labels));
    }

    /**
     * Get the background color for the word
     */
    static function commentColor(string $wordNumber) : string
    {
        $commentHandling = Dependencies::getInstance()->commentHandling();
        
        $comments = [];
        foreach(self::$currentComments as $comment) {
            if ((int) $wordNumber >= $comment->getStartPosition() && (int) $wordNumber <= $comment->getEndPosition()) {
                $comments[] = $comment;
            }
        }
        return $commentHandling->getTextBackgroundColor($comments);
    }

    /**
     * Get the comments for the current paragraph
     * @return \DOMElement
     * @throws \DOMException
     */
    static function getCurrentComments(): \DOMElement 
    {
        $commentHandling = Dependencies::getInstance()->commentHandling();
        $html = $commentHandling->getCommentsHtml(self::$currentComments);

        $doc = new DOMDocument;
        $doc->loadXML('<root xml:id="root">' . $html . '</root>');
        return $doc->getElementById('root');
    }
}
