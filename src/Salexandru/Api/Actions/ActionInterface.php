<?php

namespace Salexandru\Api\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface ActionInterface
{

    public function run(Request $req, Response $res, array $args);
}
