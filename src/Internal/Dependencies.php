<?php

namespace Edutiek\LongEssayAssessmentService\Internal;

use Edutiek\LongEssayAssessmentService\Base\PdfGeneration;

class Dependencies
{
    private static self $instance;
    
    
    /** @var Authentication */
    protected $authentication;

    /** @var HtmlProcessing */
    protected $htmlProcessing;

    /** @var ImageProcessing */
    protected $imageProcessing;

    /** @var PdfGeneration */
    protected $pdfGeneration;

    /** @var CommentHandling */
    protected $commentHandling;

    /**
     * Get the instance
     */
    public static function getInstance() :self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }   
        return self::$instance;
    }

    /**
     * Get the current service version
     * This is saved for written essays and relevant for their correction comments
     * By convention the version number is a coded date of the last relevant service change
     */
    public function serviceVersion() : int
    {
        return 20240603;
    }
    
    /**
     * Get the object for authentication
     */
    public function auth() : Authentication
    {
        if (!isset($this->authentication)) {
            $this->authentication = new Authentication();
        }
        return $this->authentication;
    }


    /**
     * Get the object for comment handling
     */
    public function commentHandling() : CommentHandling
    {
        if (!isset($this->commentHandling)) {
            $this->commentHandling = new CommentHandling();
        }
        return $this->commentHandling;
    }

    /**
     * Get the object for HTML processing
     */
    public function html() : HtmlProcessing
    {
        if (!isset($this->htmlProcessing)) {
            $this->htmlProcessing = new HtmlProcessing();
        }

        return $this->htmlProcessing;
    }

    /**
     * Get the object for Image processing
     */
    public function image(): ImageProcessing 
    {
        if (!isset($this->imageProcessing)) {
            $this->imageProcessing = new ImageProcessing();
        }

        return $this->imageProcessing;
    }

    /**
     * Get the object for PDG generation
     */
    public function pdfGeneration() : PdfGeneration
    {
        if (!isset($this->pdfGeneration)) {
            $this->pdfGeneration = new PdfGeneration();
        }
        return $this->pdfGeneration;
    }

}