<?php

namespace Edutiek\LongEssayAssessmentService\Base;

use Edutiek\LongEssayAssessmentService\Data\EnvResource;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Internal\Authentication;
use Edutiek\LongEssayAssessmentService\Internal\Dependencies;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;


/**
 * Base class for the REST handlers of the writer and corrector apps
 */
abstract class BaseRest extends App
{
    /** @var BaseContext */
    protected $context;

    /** @var Dependencies */
    protected $dependencies;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var array */
    protected $args;

    /** @var array */
    protected $params;

    /** @var string */
    protected $purpose;

    /**
     * Init server / add handlers
     */
    public function init(BaseContext $context, Dependencies $dependencies)
    {
        $this->context = $context;
        $this->dependencies = $dependencies;
    }


    /**
     * Prepare the request processing (access check, init of properties)
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param string $purpose 'data' or 'file'
     * @return bool
     */
    protected function prepare(Request $request, Response $response, array $args, string $purpose): bool
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $this->params = $request->getParams();
        $this->purpose = $purpose;

        $user_key = $this->params['LongEssayUser'];
        $env_key = $this->params['LongEssayEnvironment'];
        $signature = $this->params['LongEssaySignature'];

        if (empty($user_key)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'missing LongEssayUser param');
            return false;
        }
        if (empty($env_key)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'missing LongEssayEnvironment param');
            return false;
        }
        if (empty($signature)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'missing LongEssaySignature param');
            return false;
        }

        try {
            $this->context->init($user_key, $env_key);
        }
        catch (ContextException $e) {
            $this->setResponseForContextException($e);
            return false;
        }
        catch (\Throwable $t) {
            $this->setResponse(StatusCode::HTTP_BAD_REQUEST, $t->getMessage());
            return false;
        }

        $token = $this->context->getApiToken($purpose);

        if (!isset($token)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'current token is not found');
            return false;
        }
        if (!$this->dependencies->auth()->checkTokenValid($token)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'current token is expired');
            return false;
        }
//        todo: client ip may change in mobile clients, perhaps make the check configurable
//        if (!$this->dependencies->auth()->checkRemoteAddress($token)) {
//            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'client ip is not valid');
//            return false;
//        }
        if (!$this->dependencies->auth()->checkSignature($token, $user_key, $env_key, $signature)) {
            $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, 'signature is wrong');
            return false;
        }

        return true;
    }

    /**
     * Set a new expiration time for the data token and set it in the response
     */
    protected function refreshDataToken()
    {
        $token = $this->context->getApiToken(Authentication::PURPOSE_DATA);
        $token = $token->withExpires($this->dependencies->auth()->getTokenExpireTime(Authentication::PURPOSE_DATA));
        $this->context->setApiToken($token, Authentication::PURPOSE_DATA);
        $this->response = $this->response->withHeader('LongEssayDataToken', $token->getValue());
    }


    /**
     * Generate a new data token and set it in the response
     */
    protected function setNewDataToken()
    {
        $token = $this->dependencies->auth()->generateApiToken(Authentication::PURPOSE_DATA);
        $this->context->setApiToken($token, Authentication::PURPOSE_DATA);
        $this->response = $this->response->withHeader('LongEssayDataToken', $token->getValue());
    }

    /**
     * Generate a new file token and set it in the response
     */
    protected function setNewFileToken()
    {
        $token = $this->dependencies->auth()->generateApiToken(Authentication::PURPOSE_FILE);
        $this->context->setApiToken($token, Authentication::PURPOSE_FILE);
        $this->response = $this->response->withHeader('LongEssayFileToken', $token->getValue());

    }

    /**
     * Modify the response with a status code and json return
     * @param int      $status
     * @param string|array $json
     * @return Response
     */
    protected function setResponse(int $status,  $json = []): Response
    {
        return $this->response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('LongEssayTime', (string) time())
            ->withStatus($status)
            ->withJson($json);
    }

    /**
     * Set the response according to a context exception
     */
    protected function setResponseForContextException(ContextException $e) : Response
    {
        switch ($e->getCode()) {
            case ContextException::USER_NOT_VALID:
                return $this->setResponse(StatusCode::HTTP_UNAUTHORIZED, $e->getMessage());

            case ContextException::ENVIRONMENT_NOT_VALID:
                return $this->setResponse(StatusCode::HTTP_NOT_FOUND, $e->getMessage());

            case ContextException::PERMISSION_DENIED:
                return $this->setResponse(StatusCode::HTTP_FORBIDDEN, $e->getMessage());

            case ContextException::SERVICE_UNAVAILABLE:
                return $this->setResponse(StatusCode::HTTP_SERVICE_UNAVAILABLE, $e->getMessage());

            default:
                return $this->setResponse(StatusCode::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }


    /**
     * GET a resource file (sent as inline resource)
     */
    public function getFile(Request $request, Response $response, array $args): Response
    {
        // common checks and initializations
        if (!$this->prepare($request, $response, $args, Authentication::PURPOSE_FILE)) {
            return $this->response;
        }

        foreach ($this->context->getResources() as $resource) {
            if ($resource->getKey() == $args['key'] && $resource->getType() != EnvResource::TYPE_URL) {
                $this->context->sendFileResource($resource->getKey());
                return $response;
            }
        }

        return $this->setResponse(StatusCode::HTTP_NOT_FOUND, 'resource not found');
    }


}