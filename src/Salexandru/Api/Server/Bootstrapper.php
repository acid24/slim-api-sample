<?php

namespace Salexandru\Api\Server;

use Salexandru\Api\Server;
use Salexandru\Api\Server\Exception\Handler\FallbackHandler;
use Salexandru\Api\Server\Exception\Handler\MethodNotAllowedHandler;
use Salexandru\Api\Server\Exception\Handler\NotFoundHandler;
use Salexandru\Bootstrap\ConfigInitializer;

class Bootstrapper
{

    private $server;

    public function __construct(Server $app)
    {
        $this->server = $app;
    }

    public function run()
    {
        $this->initConfig();
        $this->initContainerServices();
        $this->initRoutes();

        $this->overrideSlimHandlers();

        $this->server->run();
    }

    private function initConfig()
    {
        $configInitializer = new ConfigInitializer($this->server->getContainer());
        $configInitializer->run();
    }

    private function initContainerServices()
    {

    }

    private function initRoutes()
    {

    }

    private function overrideSlimHandlers()
    {
        /** @var \ArrayAccess $container */
        $container = $this->server->getContainer();

        $container['errorHandler'] = function () {
            return new FallbackHandler();
        };
        $container['notAllowedHandler'] = function () {
            return new MethodNotAllowedHandler();
        };
        $container['notFoundHandler'] = function () {
            return new NotFoundHandler();
        };
    }
}
