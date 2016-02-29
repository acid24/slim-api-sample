<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Salexandru\Api\Server as ApiServer;
use Salexandru\Api\Server\Exception\InvalidAccessTokenException;
use Salexandru\Api\Server\Exception\InvalidJsonSyntaxException;
use Salexandru\Api\Server\Exception\MissingAccessTokenException;
use Salexandru\Api\Server\Exception\MissingContentTypeException;
use Salexandru\Api\Server\Exception\UnsupportedMediaTypeException;
use Salexandru\Api\Server\Response\JsonResponseTrait;
use Salexandru\Api\Server\Response\LoggingContextTrait;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface as Logger;

class FallbackHandler
{

    use JsonResponseTrait;
    use LoggingContextTrait;

    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $req, Response $res, \Exception $exception)
    {
        switch (true) {
            case $exception instanceof MissingContentTypeException:
                $response = $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MISSING_CONTENT_TYPE,
                    'message' => $exception->getMessage(),
                    'status' => 400
                ]);
                break;
            case $exception instanceof UnsupportedMediaTypeException:
                $response = $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_UNSUPPORTED_MEDIA_TYPE,
                    'message' => $exception->getMessage(),
                    'status' => 415
                ]);
                break;
            case $exception instanceof InvalidJsonSyntaxException:
                $response = $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MALFORMED_INPUT_SYNTAX,
                    'message' => $exception->getMessage(),
                    'status' => 400
                ]);
                break;
            case $exception instanceof MissingAccessTokenException:
                $response = $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_MISSING_ACCESS_TOKEN,
                    'message' => $exception->getMessage(),
                    'status' => 401
                ]);
                break;
            case $exception instanceof InvalidAccessTokenException:
                $response = $this->buildErrorResponse($res, [
                    'code' => ApiServer::ERROR_INVALID_ACCESS_TOKEN,
                    'message' => $exception->getMessage(),
                    'status' => 401
                ]);
                break;
            default:
                $response = $this->buildErrorResponse($res);
                break;
        }

        $context = $this->getLoggingContextFor($response);
        $this->logger->info('Sent {status} response with body {body}', $context);

        return $response;
    }
}
