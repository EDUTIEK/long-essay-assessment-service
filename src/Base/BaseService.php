<?php

namespace Edutiek\LongEssayAssessmentService\Base;

use Edutiek\LongEssayAssessmentService\Data\PageImage;
use Edutiek\LongEssayAssessmentService\Data\PdfSettings;
use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Data\PdfElement;
use Edutiek\LongEssayAssessmentService\Data\PdfPart;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;
use Edutiek\LongEssayAssessmentService\Base\PdfGeneration;

/**
 * Common API of the Writer and Corrector services
 * @package Edutiek\LongEssayAssessmentService\Internal
 */
abstract class BaseService
{
    /**
     * @const node module name of the frontend
     */
    protected const FRONTEND_MODULE = '';

    /** @var BaseContext  */
    protected $context;

    /** @var Dependencies */
    protected $dependencies;

    /**
     * Service constructor.
     * A class implementing the Context interface must be provided by the LMS for this service
     *
     * @param BaseContext $context
     */
    public function __construct(BaseContext $context)
    {
        $this->context = $context;
        $this->dependencies = new Dependencies();
    }

    /**
     * Get the URL of the frontent, relative to the service root directory, without starting slash
     * This add also a query string with the revision to avoid an outdated cached app
     * @return void
     */
    public static function getFrontendRelativeUrl() : string
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../package-lock.json'), true);
        $resolved = $json['packages']['node_modules/' . static::FRONTEND_MODULE]['resolved'] ?? '';
        $revision = (string) parse_url($resolved, PHP_URL_FRAGMENT);

        return 'node_modules/' . static::FRONTEND_MODULE . '/dist/index.html?' . substr($revision, 0, 7);
    }


    /**
     * Add the necessary parameters for the frontend and send a redirection to it
     */
    public function openFrontend()
    {
        $token = $this->dependencies->auth()->generateApiToken(Authentication::PURPOSE_DATA);
        $this->context->setApiToken($token, Authentication::PURPOSE_DATA);

        $this->setFrontendParam('Backend', $this->context->getBackendUrl());
        $this->setFrontendParam('Return', $this->context->getReturnUrl());
        $this->setFrontendParam('User', $this->context->getUserKey());
        $this->setFrontendParam('Environment', $this->context->getEnvironmentKey());
        $this->setFrontendParam('Token', $token->getValue());

        $this->setSpecificFrontendParams();

        // use this if browsers prevent cookies being saved for a redirection
        // $this->redirectByHtml($this->context->getFrontendUrl());

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Location: ' . $this->context->getFrontendUrl());
        exit;
    }

    /**
     * Set specific frontend params required by an app
     * e.g. the current item key for the corrector app
     */
    abstract protected function setSpecificFrontendParams();


    /**
     * Handle a REST like request from the Web App
     */
    abstract public function handleRequest();


    /**
     * Set a parameter for the frontend
     *
     * Parameters are sent as cookies over https
     * They are only needed when the frontend is initialized and can expire afterwards (1 minute)
     * They should be set for the whole server path to allow a different frontend locations during development
     *
     * @param $name
     * @param $value
     */
    protected function setFrontendParam($name, $value)
    {
        setcookie(
            'LongEssay' . $name, $value, [
                'expires' => time() + 60,
                'path' => '/',
                'domain' => '',
                'secure' => (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? true : false,
                'httponly' => false,
                'sameSite' => 'Strict' // None, Lax, Strict
            ]
        );
    }

    /**
     * Deliver a redirecting HTML page
     * use this if browsers prevent cookies being saved for a redirection
     * @param string $url
     */
    protected function redirectByHtml($url)
    {
        echo '<!DOCTYPE html>
            <html>
            <head>
               <meta http-equiv="refresh" content="0; url=$url">
            </head>
            <body>
               <a href="$url">Redirect to $url ...</a>
            </body>
            </html>';
        exit;
    }

    /**
     * Format a date or a timespan given by unix timestamps in the context timezone
     */
    protected function formatDates(?int $start = null, ?int $end = null) : string
    {
        $parts = [];
        foreach ([$start, $end] as $date) {
            if (!empty($date)) {
                $date = (new \DateTimeImmutable())
                    ->setTimezone(new \DateTimeZone($this->context->getTimezone()))
                    ->setTimestamp($date);

                if ($this->context->getLanguage() == 'de') {
                    $parts[] = $date->format('d.m.Y H:i');
                }
                else {
                    $parts[] = $date->format('Y-m-d H:i');
                }
            }
        }

        return implode(' - ', $parts);
    }

    /**
     * Get the object for PDG generation
     */
    public function getPdfGeneration() : PdfGeneration
    {
        return $this->dependencies->pdfGeneration();
    }

    /**
     * Get the path of a writer page image for pdf processing
     * @param PageImage|null $image
     * @return string
     */
    protected function getPageImagePathForPdf(?PageImage $image) : string
    {
        if (isset($image)) {
            return $this->dependencies->image()->getImageSrcAsPathForTCPDF(
                $image,
                $this->context->getAbsoluteTempPath(),
                $this->context->getRelativeTempPath()
            );
        }
        return '';
    }

    /**
     * Get a standard pdf part
     * It may consist of several elements and span over several pages
     * It respects the pdf settings from the service context
     *
     * @param PdfElement[] $elements
     */
    public function getStandardPdfPart(array $elements = [], ?PdfSettings $pdfSettings = null): PdfPart
    {
        if (!isset($pdfSettings)) {
            $pdfSettings = $this->context->getPdfSettings();
        }

        return (new PdfPart(
           PdfPart::FORMAT_A4,
           PdfPart::ORIENTATION_PORTRAIT,
            $elements
        ))  ->withTopMargin($pdfSettings->getContentTopMargin())
            ->withBottomMargin($pdfSettings->getContentBottomMargin())
            ->withLeftMargin($pdfSettings->getLeftMargin())
            ->withRightMargin($pdfSettings->getRightMargin())
            ->withHeaderMargin($pdfSettings->getHeaderMargin())
            ->withFooterMargin($pdfSettings->getFooterMargin())
            ->withPrintHeader($pdfSettings->getAddHeader())
            ->withPrintFooter($pdfSettings->getAddFooter());
    }
}