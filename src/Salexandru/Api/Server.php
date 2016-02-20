<?php

namespace Salexandru\Api;

use Slim\App as SlimApp;
use Salexandru\Api\Server\Bootstrapper;

class Server extends SlimApp
{

    const ERROR_GENERIC = 1;
    const ERROR_METHOD_NOT_ALLOWED = 2;
    const ERROR_RESOURCE_NOT_FOUND = 3;
    const ERROR_MISSING_CONTENT_TYPE = 4;
    const ERROR_UNSUPPORTED_MEDIA_TYPE = 5;
    const ERROR_MALFORMED_INPUT_SYNTAX = 6;
    const ERROR_MISSING_ACCESS_TOKEN = 7;
    const ERROR_INVALID_ACCESS_TOKEN = 8;
    const ERROR_UNEXPECTED_INPUT = 9;

    public function bootstrap()
    {
        return new Bootstrapper($this);
    }
}
