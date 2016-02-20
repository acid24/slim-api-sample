<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Salexandru\Api\Server as ApiServer;
use Salexandru\Api\Server\Response\JsonResponseTrait;
use Slim\Http\Request;
use Slim\Http\Response;

class MethodNotAllowedHandler
{

    use JsonResponseTrait;

    public function __invoke(Request $req, Response $res, array $allowedMethods)
    {
        return $this->buildErrorResponse($res, [
            'code' => ApiServer::ERROR_METHOD_NOT_ALLOWED,
            'message' => sprintf('Method %s is not allowed for this resource', $req->getMethod()),
            'status' => 405
        ])->withHeader('Allow', implode(', ', $allowedMethods));
    }
}
