<?php

namespace Salexandru\Api\Server;

use Salexandru\Api\Server;
use Salexandru\Bootstrap\ConfigInitializer;

class Bootstrap
{

    private $server;

    public function __construct(Server $app)
    {
        $this->server = $app;
    }

    public function run()
    {
        $this->initConfig();

        $this->server->run();
    }

    private function initConfig()
    {
        $configInitializer = new ConfigInitializer($this->server->getContainer());
        $configInitializer->run();
    }
}
