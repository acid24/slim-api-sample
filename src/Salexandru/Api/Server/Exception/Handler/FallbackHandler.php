<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Salexandru\Api\Server as ApiServer;
use Salexandru\Api\Server\Exception\InvalidAccessTokenException;
use Salexandru\Api\Server\Exception\InvalidJsonSyntaxException;
use Salexandru\Api\Server\Exception\MissingAccessTokenException;
use Salexandru\Api\Server\Exception\MissingContentTypeException;
use Salexandru\Api\Server\Exception\UnsupportedMediaTypeException;
use Salexandru\Api\Server\Response\JsonResponseTrait;
use Slim\Http\Request;
use Slim\Http\Response;

class FallbackHandler
{

    use JsonResponseTrait;

    public function __invoke(Request $req, Response $res, \Exception $exception)
    {
        switch (true) {
            case $exception instanceof MissingContentTypeException:
                return $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MISSING_CONTENT_TYPE,
                    'message' => $exception->getMessage(),
                    'status' => 400
                ]);
            case $exception instanceof UnsupportedMediaTypeException:
                return $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_UNSUPPORTED_MEDIA_TYPE,
                    'message' => $exception->getMessage(),
                    'status' => 415
                ]);
            case $exception instanceof InvalidJsonSyntaxException:
                return $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MALFORMED_INPUT_SYNTAX,
                    'message' => $exception->getMessage(),
                    'status' => 400
                ]);
            case $exception instanceof MissingAccessTokenException:
                return $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MISSING_ACCESS_TOKEN,
                    'message' => $exception->getMessage(),
                    'status' => 401
                ]);
            case $exception instanceof InvalidAccessTokenException:
                return $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_INVALID_ACCESS_TOKEN,
                    'message' => $exception->getMessage(),
                    'status' => 401
                ]);
            default:
                return $this->buildErrorResponse($res);
        }
    }
}
