<?php

namespace Salexandru\Api\Server;

use Interop\Container\ContainerInterface as Container;
use Salexandru\Api\Server;
use Salexandru\Api\Server\Bootstrap\ContainerServicesProvider;
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
        $this->initContainerServices();
        $this->initRoutes();

        $this->addServerLevelMiddleware();

        $this->server->run();
    }

    private function initContainerServices()
    {
        /** @var \Pimple\Container $container */
        $container = $this->server->getContainer();
        // Take advantage of the fact that Slim uses Pimple container which gives us this
        // nice way of registering container services
        $provider = new ContainerServicesProvider();
        $provider->register($container);
    }

    private function initRoutes()
    {
        /** @var Container $container */
        $container = $this->server->getContainer();

        $defaultRequestVetting = $container->get('middleware.requestVetting.default');
        $tokenlessRequestVetting = $container->get('middleware.requestVetting.noAccessToken');

        $this->server->post('/tokens/actions/issue', 'actions.issueAccessToken')
            ->add($tokenlessRequestVetting);
        $this->server->post('/tokens/actions/refresh', 'actions.refreshAccessToken')
            ->add($tokenlessRequestVetting);
    }

    private function addServerLevelMiddleware()
    {
        /** @var Container $container */
        $container = $this->server->getContainer();
        $this->server->add($container->get('middleware.responseLogging'));
        $this->server->add($container->get('middleware.requestLogging'));
    }
}
