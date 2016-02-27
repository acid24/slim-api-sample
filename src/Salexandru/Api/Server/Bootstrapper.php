<?php

namespace Salexandru\Api\Server;

use Interop\Container\ContainerInterface as Container;
use Psr\Log\LoggerInterface as Logger;
use Salexandru\Api\Middleware\RequestLoggingMiddleware;
use Salexandru\Api\Middleware\RequestVettingMiddleware;
use Salexandru\Api\Server;
use Salexandru\Api\Server\Exception\Handler\FallbackHandler;
use Salexandru\Api\Server\Exception\Handler\MethodNotAllowedHandler;
use Salexandru\Api\Server\Exception\Handler\NotFoundHandler;
use Salexandru\Bootstrap\ConfigInitializer;
use Salexandru\Bootstrap\LoggingInitializer;
use Salexandru\Jwt\Adapter\Configuration as AdapterConfiguration;
use Salexandru\Jwt\Adapter\LcobucciAdapter as JwtAdapter;
use Salexandru\Jwt\AdapterInterface;
use Slim\Collection;

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

        $this->server->add($this->server->getContainer()->get('middleware.requestLogging'));
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
        /** @var \ArrayAccess $container */
        $container = $this->server->getContainer();

        $container['jwtAdapter'] = function (Container $c) {
            /** @var Collection $settings */
            $settings = $c->get('settings');

            $adapterConfiguration = AdapterConfiguration::loadFromArray($settings->get('jwt'));
            return new JwtAdapter($adapterConfiguration);
        };

        $container['middleware.requestVetting.default'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RequestVettingMiddleware($jwtAdapter);
        };

        $container['middleware.requestVetting.noAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RequestVettingMiddleware($jwtAdapter, ['requiresAccessToken' => false]);
        };

        $container['middleware.requestLogging'] = function (Container $c) {
            /** @var Logger $logger */
            $logger = $c->get('logger.http');
            return new RequestLoggingMiddleware($logger);
        };
    }

    private function initRoutes()
    {
        /** @var Container $container */
        $container = $this->server->getContainer();

        $defaultRequestVetting = $container->get('middleware.requestVetting.default');
        $tokenlessRequestVetting = $container->get('middleware.requestVetting.noAccessToken');

        $this->server->post('/tokens/actions/issue', 'actions.issueAccessToken:run')
            ->add($tokenlessRequestVetting);
        $this->server->post('/tokens/actions/refresh', 'actions.refreshAccessToken:run')
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
