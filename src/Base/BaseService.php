<?php

namespace Edutiek\LongEssayAssessmentService\Base;

use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;

/**
 * Common API of the Writer and Corrector services
 * @package Edutiek\LongEssayAssessmentService\Internal
 */
abstract class BaseService
{
    /**
     * @const Path of the frontend web app, relative to the service root directory, without starting slash
     */
    public const FRONTEND_RELATIVE_PATH = '';

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
        //$this->redirectByHtml($this->context->getFrontendUrl());

        header('Location: ' . $this->context->getFrontendUrl());
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
                    ->setTimestamp($start);

                if ($this->context->getLanguage() == 'de') {
                    $parts[] = $date->format('d.m.Y H:i:s');
                }
                else {
                    $parts[] = $date->format('Y-m-d H:i:s');
                }
            }
        }

        return implode(' - ', $parts);
    }
}