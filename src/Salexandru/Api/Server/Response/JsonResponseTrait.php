<?php

namespace Salexandru\Api\Server\Response;

use Slim\Http\Response;
use Salexandru\Api\Server as ApiServer;

trait JsonResponseTrait
{

    protected function buildResponse(Response $res, $data)
    {
        return $res->withJson(['data' => $data]);
    }

    protected function buildErrorResponse(Response $res, array $options = null)
    {
        $defaults = [
            'code' => ApiServer::ERROR_GENERIC,
            'message' => 'Internal server error',
            'status' => 500
        ];
        if (null === $options) {
            $options = $defaults;
        } else {
            $options = array_merge($defaults, $options);
        }

        $data = ['error' => [
            'code' => sprintf('ERR-%06d', $options['code']),
            'message' => $options['message']
        ]];

        return $res->withJson($data, $options['status']);
    }
}
