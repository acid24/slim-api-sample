<?php

namespace Salexandru\Api\Server;

use Salexandru\Api\Server;

class Bootstrap
{

    private $server;

    public function __construct(Server $app)
    {
        $this->server = $app;
    }

    public function run()
    {
        $this->server->run();
    }
}
