<?php

namespace Edutiek\LongEssayAssessmentService\Internal;

use Edutiek\LongEssayAssessmentService\Data\CorrectionPage;
use Edutiek\LongEssayAssessmentService\Data\PageImage;
use LongEssayPDFConverter\ImageMagick\PDFImage;
use Imagick;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use LongEssayImageSketch\ImageMagick\Sketch;
use LongEssayImageSketch\Shape;
use Edutiek\LongEssayAssessmentService\Data\CorrectionMark;
use LongEssayImageSketch\Point;

class ImageProcessing
{
    /**
     * Create page images from a pdf file
     * @param resource $pdf - file handlers of pdf file
     * @return PageImage[]
     */
    public function createImagesFromPdf($pdf) : array
    {
        $PDFImage = new PDFImage();
        $magic = new Imagick();

        $images = [];
        $resources = $PDFImage->asOnePerPage($pdf, PDFImage::NORMAL);
        $thumbnails = $PDFImage->asOnePerPage($pdf, PDFImage::THUMBNAIL);

        foreach ($resources as $index => $resource) {
            $magic->readImageFile($resource);
            $magic->resetIterator();
            $mime = $magic->getImageMimeType();
            $width = $magic->getImageWidth();
            $height = $magic->getImageHeight();
            $magic->removeImage();
            
            $thumbnail = null;
            $thumb_mime = null;
            $thumb_width = null;
            $thumb_height = null;
            
            if (isset($thumbnails[$index])) {
                $thumbnail = $thumbnails[$index];
                $magic->clear();
                $magic->readImageFile($thumbnail);
                $magic->resetIterator();
                $thumb_mime = $magic->getImageMimeType();
                $thumb_width = $magic->getImageWidth();
                $thumb_height = $magic->getImageHeight();
                $magic->removeImage();
            }
  
            $images[] = new PageImage(
                $resource,
                $mime,
                $width,
                $height,
                $thumbnail,
                $thumb_mime,
                $thumb_width,
                $thumb_height
            );
        }

        return $images;
    }


    /**
     * Get the data that can be used as src for an image
     */
    public function getImageSrcAsData(PageImage $page_image) 
    {
        $content = stream_get_contents($page_image->getImage());
        $mime = $page_image->getMime();
        $base64 = base64_encode($content);
        return "data:{$mime};base64,{$base64}";
    }

    /**
     * Get the data that can be used as src for an image
     * This avoids working with file pathes but may cause out of memory with Mustache
     */
    public function getImageSrcAsDataForTCPDF(PageImage $page_image)
    {
        $content = stream_get_contents($page_image->getImage());
        $base64 = base64_encode($content);
        return "@{$base64}";
    }
    
    /**
     * Get a file path for the image
     * @param PageImage$image
     * @param string $create_dir - directory where the image is created
     * @param string $src_path - path which is used in the src attribute o an img tag                   
     */
    public function getImageSrcAsPathForTCPDF(PageImage $image, string $create_dir, string $src_path)
    {
        $content = stream_get_contents($image->getImage());
        $file = tempnam($create_dir, 'LAS');
        file_put_contents ($file, $content);
        return $src_path . '/' . basename($file);
    }

    /**
     * Apply the marks of comments to a page image
     * @param CorrectionComment[] $comments
     */
    public function applyCommentsMarks(CorrectionPage $page, PageImage $image, array $comments) :PageImage
    {
        $sketch = new Sketch('PNG');
        
        $shapes = [];
        foreach ($comments as $comment) {
            if ($comment->getParentNumber() == $page->getPageNo() && !empty($comment->getMarks())) {
                foreach ($comment->getMarks() as $mark) {
                    $shapes[] = $this->getShapeFromMark($mark, '', $this->getShapeColor($mark, $comment));
                }
            }
        }
        if (!empty($shapes)) {
            $sketched = $sketch->applyShapes($shapes, $image->getImage());
            return new PageImage($sketched, 'image/x-png', $image->getWidth(), $image->getHeight());
        }
        else {
            return $image;
        }
    }
    
    
    protected function getShapeFromMark(CorrectionMark $mark, string $label, string $color) : Shape
    {
        $pos = new Point($mark->getPos()->getX(), $mark->getPos()->getY());
        
        switch($mark->getShape()) {
            case CorrectionMark::SHAPE_LINE:
                $end = new Point($mark->getEnd()->getX(), $mark->getEnd()->getY());
                return new Shape\Line($end, $pos, $label, $color);
                
            case CorrectionMark::SHAPE_WAVE:
                $end = new Point($mark->getEnd()->getX(), $mark->getEnd()->getY());
                return new Shape\Wave($end, $pos, $label, $color);
                
            case CorrectionMark::SHAPE_RECTANGLE:
                $width = $mark->getWidth();
                $height = $mark->getHeight();
                return new Shape\Rectangle($width, $height, $pos, $label, $color);
                
            case CorrectionMark::SHAPE_POLYGON:
                $points = [];
                foreach($mark->getPolygon() as $point) {
                    $points[] = new Point($point->getX(), $point->getY());
                }
                return new Shape\Polygon($points, $pos, $label, $color);
                
            case CorrectionMark::SHAPE_CIRCLE:
            default:
                return new Shape\Circle($pos, $label, $color);
        }
    }
    
    
    protected function getShapeColor(CorrectionMark $mark, CorrectionComment $comment) : string
    {
        $filled = in_array($mark->getShape(), CorrectionMark::FILLED_SHAPES);
        
        switch ($comment->getRating()) {
            case CorrectionComment::RAITNG_EXCELLENT:
                return $filled ?  '#E3EFDDAA' : '#19e62e';
            case CorrectionComment::RATING_CARDINAL:
                return $filled ? '#FBDED1AA' : '#bc4710';
            default:
                return $filled ? '#D8E5F4AA' : '#3365ff';
        }
    }
}