<?php

namespace Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\EnvResource;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Data\ApiToken;
use Edutiek\LongEssayAssessmentService\Data\PageImage;
use Edutiek\LongEssayAssessmentService\Data\WritingSettings;

/**
 * Common interface for Writer and Corrector contexts
 * The context is always bound to a current user and an environment (e.g. a writing task)
 * Their keys have to be provided by init()
 */
interface BaseContext
{
    /**
     * Constructor
     */
    public function __construct();

    /**
     * Initialize the Context
     * Done by the system to open the frontend
     * Done by the service when a REST call is handled
     *
     * @param string $user_key unique key of the current user
     * @param string $environment_key unique key of the current environment
     * @return self
     * @throws ContextException
     */
    public function init(string $user_key, string $environment_key): void;

    /**
     * Get the name of the embedding system
     * This will be included in generated PDFs
     */
    public function getSystemName(): string;

    /**
     *  Get the ISO 639-1 Language Code
     *  This will be used for the writer and corrector GUI
     *  Currently 'de' and 'en' are supported, all other default to 'en'
     */
    public function getLanguage(): string;


    /**
     * Get the timezone identifier, e.g. 'Europe/Berlin'
     * This will be used for date and time display
     */
    public function getTimezone(): string;


    /**
     * Get the Url of the frontend
     * This URL should point to the index.html of the frontend
     * Standard is to use the base URL of the installed LongEssayAssessmentService and add the FRONTEND_RELATIVE_PATH
     * @see Service::FRONTEND_RELATIVE_PATH
     */
    public function getFrontendUrl(): string;


    /**
     * Get the URL of the backend
     * This URL of the system will get REST requests from the frontend
     * The system should then hand over the request to the service
     */
    public function getBackendUrl(): string;

    /**
     * Get the return url of the system
     * This URL of the system will be called when the frontend is closed
     */
    public function getReturnUrl(): string;


    /**
     * Get the identifying key of the current user
     */
    public function getUserKey(): string;


    /**
     * Get the identifying key of the current environment
     */
    public function getEnvironmentKey(): string;


    /**
     * Get the api token for the context
     * This is used for the authorization of REST calls
     * Only one valid api token should exist for the current user, task and purpose
     * @param string $purpose   'data' or 'file'
     */
    public function getApiToken(string $purpose): ?ApiToken;


    /**
     * Set a new api token for the context
     * This is used when a frontend is opened
     * It should overwrite an existing api token of the current user, task and purpose
     * This will make REST calls from already opened frontends for the same context invalid
     * @param string $purpose   'data' or 'file'
     */
    public function setApiToken(ApiToken $api_token, string $purpose);
    
    /**
     * Extend the session of an authenticated user
     * This is called when the web apps makes rest calls that indicate an activity (e.g. writing)
     * The system should then extend the user's session, if a session is open
     *
     * Note:
     * The REST interface does not need an extension, because it works with separate Api Tokens
     * But if a user returns from the web app to the system, the session may otherwise be expired
     */
    public function setAlive() : void;

    
    /**
     * Get the settings to be used for the editor
     * e.g. the headline scheme or formatting options
     */
    public function getWritingSettings(): WritingSettings;


    /**
     * Get the resources that should be available in the app
     * @return EnvResource[]
     */
    public function getResources(): array;


    /**
     * Send a file resource to the browser
     * The 'Content-Disposition' HTTP response header must be inline
     * The 'Content-Type' HTTP response header must give the correct mime type
     */
    public function sendFileResource(string $key): void;

    
    /**
     * Send a pdf page image resource to the browser
     * The 'Content-Disposition' HTTP response header must be inline
     * The 'Content-Type' HTTP response header must give the correct mime type
     */
    public function sendPageImage(string $key): void;

    /**
     * Send a thumbnail of a pdf page image resource to the browser
     * The 'Content-Disposition' HTTP response header must be inline
     * The 'Content-Type' HTTP response header must give the correct mime type
     */
    public function sendPageThumb(string $key): void;


    /**
     * Get the image of a scanned page by its key
     * Its image and thumbnail properties must be file handlers
     * @param string $key
     * @return PageImage|null
     */
    public function getPageImage(string $key): ?PageImage;

    
    /**
     * Get a path for temp files which is relative to the executing php script file
     * It must be without a trailing slash
     * The PDF generation will store temporary images there
     * TCPDF requires this path for image sources
     */
    public function getRelativeTempPath() : string;

    /**
     * Get the absolute path for temp files which must correspond to getRelativeTempPath
     * It must be without a trailing slash
     * The PDF generation will store temporary images there
     */
    public function getAbsoluteTempPath() : string;

}