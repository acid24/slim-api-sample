<?php

namespace Salexandru\Api;

use Slim\App as SlimApp;
use Salexandru\Api\Server\Bootstrap;

class Server extends SlimApp
{

    public function bootstrap()
    {
        return new Bootstrap($this);
    }
}