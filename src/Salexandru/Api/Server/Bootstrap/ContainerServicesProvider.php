<?php

namespace Salexandru\Api\Server\Bootstrap;

use Psr\Log\LoggerInterface as Logger;
use Pimple\Container as PimpleContainer;
use Interop\Container\ContainerInterface as Container;
use Pimple\ServiceProviderInterface;
use Salexandru\Api\Middleware\RequestLoggingMiddleware;
use Salexandru\Api\Middleware\RequestVettingMiddleware;
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
use Salexandru\Command\AccessToken\IssueCommand as IssueAccessTokenCommand;
use Salexandru\Command\AccessToken\RefreshCommand as RefreshAccessTokenCommand;
use Salexandru\Command\Handler\AccessToken\IssueHandler as IssueAccessTokenHandler;
use Salexandru\Command\Handler\AccessToken\RefreshHandler as RefreshAccessTokenHandler;

class ContainerServicesProvider implements ServiceProviderInterface
{


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
        $this->registerMiddlewareServices();
        $this->registerInfrastructureServices();
        $this->registerApplicationServices();
    }

    private function registerMiddlewareServices()
    {
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

    private function registerInfrastructureServices()
    {
        $commandToHandlerMap = $this->getCommandToHandlerMap();

        $container['jwtAdapter'] = function (Container $c) {
            /** @var Collection $settings */
            $settings = $c->get('settings');

            $adapterConfiguration = AdapterConfiguration::loadFromArray($settings->get('jwt'));
            return new JwtAdapter($adapterConfiguration);
        };

        $container['commandBus'] = function (Container $c) {
            return new CommandBus(new ExecutionPipelineProvider($c));
        };

        $container['commandBus.pipe.executeCommand'] = function (Container $c) use ($commandToHandlerMap) {
            return new ExecuteCommandPipe(
                new ContainerBasedHandlerLocator($c, $commandToHandlerMap),
                new HandleInflector(),
                new EndPipe()
            );
        };
    }

    private function registerApplicationServices()
    {
        $container['commandBus.handler.issueAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new IssueAccessTokenHandler($jwtAdapter);
        };

        $container['commandBus.handler.refreshAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RefreshAccessTokenHandler($jwtAdapter);
        };
    }

    private function getCommandToHandlerMap()
    {
        return [
            IssueAccessTokenCommand::class => 'commandBus.handler.issueAccessToken',
            RefreshAccessTokenCommand::class => 'commandBus.handler.refreshAccessToken'
        ];
    }
}
