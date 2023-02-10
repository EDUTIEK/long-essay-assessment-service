<?php

namespace Edutiek\LongEssayAssessmentService\Exceptions;

class ContextException extends Exception
{
    /**
     * Should be thrown when the user given by the user_key does not exist
     * Will cause an HTTP_UNAUTHORIZED (401) response
     */
    const USER_NOT_VALID = 1;

    /**
     * Should be thrown when the environment given by the environment_key does not exist (e.g. the writing task is deleted)
     * Will cause an HTTP_BAD_REQUEST (404) response
     */
    const ENVIRONMENT_NOT_VALID = 2;


    /**
     * Should be thrown when the user does not have the permission for the request (e.g. for review or stitch decision)
     * Will cause an HTTP_FORBIDDEN (403) response
     */
    const PERMISSION_DENIED = 3;


    /**
     * Can be thrown to simulate network errors, e.g. to test the web apps
     * Will cause an HTTP_SERVICE_UNAVAILABLE (503) response
     */
    const SERVICE_UNAVAILABLE = 4;
}