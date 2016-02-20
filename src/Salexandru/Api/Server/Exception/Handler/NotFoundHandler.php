<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Salexandru\Api\Server as ApiServer;
use Salexandru\Api\Server\Response\JsonResponseTrait;
use Slim\Http\Request;
use Slim\Http\Response;

class NotFoundHandler
{

    use JsonResponseTrait;

    public function __invoke(Request $req, Response $res)
    {
        return $this->buildErrorResponse($res, [
            'code' => ApiServer::ERROR_RESOURCE_NOT_FOUND,
            'message' => 'Requested resource does not exist on this server',
            'status' => 404
        ]);
    }
}
