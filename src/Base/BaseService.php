<?php

namespace Edutiek\LongEssayService\Base;

use Edutiek\LongEssayService\Internal\Dependencies;
use Edutiek\LongEssayService\Tools\HtmlProcessing;
use Edutiek\LongEssayService\Writer\Rest;

/**
 * Common API of the Writer and Corrector services
 * @package Edutiek\LongEssayService\Internal
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
        $token = $this->dependencies->auth()->generateApiToken($this->context->getDefaultTokenLifetime());
        $this->context->setApiToken($token);

        $this->setFrontendParam('Backend', $this->context->getBackendUrl());
        $this->setFrontendParam('Return', $this->context->getReturnUrl());
        $this->setFrontendParam('User', $this->context->getUserKey());
        $this->setFrontendParam('Environment', $this->context->getEnvironmentKey());
        $this->setFrontendParam('Token', $token->getValue());

        // use this if browsers prevent cookies being saved for a redirection
        //$this->redirectByHtml($this->context->getFrontendUrl());

        header('Location: ' . $this->context->getFrontendUrl());
    }

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
                'secure' => true,
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
        echo <<<END
            <!DOCTYPE html>
            <html>
            <head>
               <meta http-equiv="refresh" content="0; url=$url">
            </head>
            <body>
               <a href="$url">Redirect to $url ...</a>
            </body>
            </html>'
            END;
        exit;
    }
}