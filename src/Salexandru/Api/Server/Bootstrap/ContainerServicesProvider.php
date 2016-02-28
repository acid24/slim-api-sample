<?php

namespace Salexandru\Api\Server\Bootstrap;

use Psr\Log\LoggerInterface as Logger;
use Pimple\Container as PimpleContainer;
use Interop\Container\ContainerInterface as Container;
use Pimple\ServiceProviderInterface;
use Salexandru\Api\Middleware\RequestLoggingMiddleware;
use Salexandru\Api\Middleware\RequestVettingMiddleware;
use Salexandru\Api\Middleware\ResponseLoggingMiddleware;
use Salexandru\Api\Server\Exception\Handler\FallbackHandler;
use Salexandru\Api\Server\Exception\Handler\MethodNotAllowedHandler;
use Salexandru\Api\Server\Exception\Handler\NotFoundHandler;
use Salexandru\CommandBus\CommandBus;
use Salexandru\CommandBus\Handler\ContainerBasedHandlerLocator;
use Salexandru\CommandBus\Handler\HandleInflector;
use Salexandru\CommandBus\Pipeline\EndPipe;
use Salexandru\CommandBus\Pipeline\ExecuteCommandPipe;
use Salexandru\CommandBus\Pipeline\ExecutionPipelineProvider;
use Salexandru\Jwt\AdapterInterface;
use Slim\Collection;
use Salexandru\Jwt\Adapter\Configuration as AdapterConfiguration;
use Salexandru\Jwt\Adapter\LcobucciAdapter as JwtAdapter;
use Salexandru\Command\Handler\AccessToken\IssueHandler as IssueAccessTokenHandler;
use Salexandru\Command\Handler\AccessToken\RefreshHandler as RefreshAccessTokenHandler;

class ContainerServicesProvider implements ServiceProviderInterface
{

    /**
     * @var \ArrayAccess
     */
    private $container;

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Pimple\Container $container A container instance
     */
    public function register(PimpleContainer $container)
    {
        $this->setContainer($container);

        $this->registerExceptionHandlers();
        $this->registerMiddleware();
        $this->registerInfrastructureServices();
        $this->registerApplicationServices();
    }

    private function setContainer(\ArrayAccess $container)
    {
        $this->container = $container;
    }

    private function registerExceptionHandlers()
    {
        $this->container['errorHandler'] = function () {
            return new FallbackHandler();
        };
        $this->container['notAllowedHandler'] = function () {
            return new MethodNotAllowedHandler();
        };
        $this->container['notFoundHandler'] = function () {
            return new NotFoundHandler();
        };
    }

    private function registerMiddleware()
    {
        $this->container['middleware.requestVetting.default'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RequestVettingMiddleware($jwtAdapter);
        };

        $this->container['middleware.requestVetting.noAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RequestVettingMiddleware($jwtAdapter, ['requiresAccessToken' => false]);
        };

        $this->container['middleware.requestLogging'] = function (Container $c) {
            /** @var Logger $logger */
            $logger = $c->get('logger.http');
            return new RequestLoggingMiddleware($logger);
        };

        $this->container['middleware.responseLogging'] = function (Container $c) {
            /** @var Logger $logger */
            $logger = $c->get('logger.http');
            return new ResponseLoggingMiddleware($logger);
        };
    }

    private function registerInfrastructureServices()
    {
        $this->container['jwtAdapter'] = function (Container $c) {
            /** @var Collection $settings */
            $settings = $c->get('settings');

            $adapterConfiguration = AdapterConfiguration::loadFromArray($settings->get('jwt'));
            return new JwtAdapter($adapterConfiguration);
        };

        $this->container['commandBus'] = function (Container $c) {
            return new CommandBus(new ExecutionPipelineProvider($c));
        };

        $this->container['commandBus.pipe.executeCommand'] = function (Container $c) {
            return new ExecuteCommandPipe(
                new ContainerBasedHandlerLocator($c, CommandToHandlerMap::getMap()),
                new HandleInflector(),
                new EndPipe()
            );
        };
    }

    private function registerApplicationServices()
    {
        $this->container['commandBus.handler.issueAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new IssueAccessTokenHandler($jwtAdapter);
        };

        $this->container['commandBus.handler.refreshAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RefreshAccessTokenHandler($jwtAdapter);
        };
    }
}
