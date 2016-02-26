<?php

namespace Salexandru\Api\Server;

use Interop\Container\ContainerInterface as Container;
use Salexandru\Api\Server;
use Salexandru\Api\Server\Exception\Handler\FallbackHandler;
use Salexandru\Api\Server\Exception\Handler\MethodNotAllowedHandler;
use Salexandru\Api\Server\Exception\Handler\NotFoundHandler;
use Salexandru\Bootstrap\ConfigInitializer;
use Salexandru\Bootstrap\LoggingInitializer;

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
        $this->initLogging();

        $this->overrideSlimHandlers();

        $this->server->run();
    }

    private function initConfig()
    {
        $configInitializer = new ConfigInitializer($this->server->getContainer());
        $configInitializer->run();
    }

    private function initLogging()
    {
        $loggingInitializer = new LoggingInitializer($this->server->getContainer());
        $loggingInitializer->run();
    }

    private function initContainerServices()
    {

    }

    private function initRoutes()
    {
        /** @var Container $container */
        $container = $this->server->getContainer();

        $defaultRequestVetting = $container->get('middleware.requestVetting.default');
        $tokenlessRequestVetting = $container->get('middleware.requestVetting.noAccessToken');

        $this->server->post('/tokens/actions/issue', $container->get('actions.issueAccessToken'))
            ->add($tokenlessRequestVetting);
        $this->server->post('/tokens/actions/refresh', $container->get('actions.refreshAccessToken'))
            ->add($tokenlessRequestVetting);
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
