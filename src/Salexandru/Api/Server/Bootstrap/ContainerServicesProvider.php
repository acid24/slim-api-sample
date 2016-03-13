<?php

namespace Salexandru\Api\Server\Bootstrap;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Common\Proxy\Autoloader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface as PsrLogger;
use Pimple\Container as PimpleContainer;
use Interop\Container\ContainerInterface as Container;
use Pimple\ServiceProviderInterface;
use Salexandru\Api\Action\AccessToken\IssueAction as IssueAccessTokenAction;
use Salexandru\Api\Action\AccessToken\RefreshAction as RefreshAccessTokenAction;
use Salexandru\Api\Authentication\Doctrine\DbalAuthenticationStrategy;
use Salexandru\Api\Middleware\RequestLoggingMiddleware;
use Salexandru\Api\Middleware\RequestVettingMiddleware;
use Salexandru\Api\Middleware\ResponseLoggingMiddleware;
use Salexandru\Api\Server\Exception\Handler\FallbackHandler;
use Salexandru\Api\Server\Exception\Handler\MethodNotAllowedHandler;
use Salexandru\Api\Server\Exception\Handler\NotFoundHandler;
use Salexandru\Authentication\AuthenticationManager;
use Salexandru\Bootstrap\ConfigInitializer;
use Salexandru\CommandBus\CommandBus;
use Salexandru\CommandBus\CommandBusInterface;
use Salexandru\CommandBus\Handler\Resolver\DefaultHandlerResolver;
use Salexandru\CommandBus\Handler\Registry as HandlerRegistry;
use Salexandru\CommandBus\Pipeline\EndPipe;
use Salexandru\CommandBus\Pipeline\ExecuteCommandPipe;
use Salexandru\CommandBus\Pipeline\ExecutionPipelineProvider;
use Salexandru\Config\Cache\DefaultConfigCache;
use Salexandru\Db\Logging\Doctrine\DbalSqlLogger;
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

        $this->registerConfig();
        $this->registerExceptionHandlers();
        $this->registerMiddleware();
        $this->registerRouteHandlers();
        $this->registerInfrastructureServices();
        $this->registerCommandHandlers();
        $this->registerLoggers();
    }

    private function setContainer(\ArrayAccess $container)
    {
        $this->container = $container;
    }

    private function registerConfig()
    {
        // this service is needed by the config initializer
        $this->container['defaultConfigCache'] = function () {
            return new DefaultConfigCache(new ApcuCache());
        };

        (new ConfigInitializer($this->container))->run();
    }

    private function registerLoggers()
    {
        $path = sys_get_temp_dir() . '/app.log';
        $level = 'debug';
        $format = LineFormatter::SIMPLE_FORMAT;
        $dateFormat = LineFormatter::SIMPLE_DATE;

        extract($this->container['settings']->get('logging'));

        $map = [
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'warn' => Logger::WARNING,
            'error' => Logger::ERROR,
            'err' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY
        ];

        $formatter = new LineFormatter(trim($format) . PHP_EOL, $dateFormat);

        $streamHandler = new StreamHandler($path, $map[$level]);
        $streamHandler->setFormatter($formatter);
        $processor = new PsrLogMessageProcessor();

        $this->container['logger.app'] = function () use ($streamHandler, $processor) {
            return new Logger('APP', [$streamHandler], [$processor]);
        };
        $this->container['logger.http'] = function () use ($streamHandler, $processor) {
            return new Logger('HTTP', [$streamHandler], [$processor]);
        };
        $this->container['logger.sql'] = function () use ($streamHandler, $processor) {
            return new Logger('SQL', [$streamHandler], [$processor]);
        };
    }

    private function registerExceptionHandlers()
    {
        $this->container['errorHandler'] = function (Container $c) {
            /** @var PsrLogger $logger */
            $logger = $c->get('logger.http');
            return new FallbackHandler($logger);
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
            /** @var PsrLogger $logger */
            $logger = $c->get('logger.http');
            return new RequestLoggingMiddleware($logger);
        };

        $this->container['middleware.responseLogging'] = function (Container $c) {
            /** @var PsrLogger $logger */
            $logger = $c->get('logger.http');
            return new ResponseLoggingMiddleware($logger);
        };
    }

    private function registerInfrastructureServices()
    {
        $this->container['dbConnection'] = function (Container $c) {
            /** @var Collection $settings */
            $settings = $c->get('settings');
            $db = $settings->get('db');

            $params = [
                'driver' => $db['driver'],
                'path' => $db['path']
            ];

            $configuration = new DbalConfiguration();
            if ($db['loggingEnabled'] == true) {
                /** @var PsrLogger $logger */
                $logger = $c->get('logger.sql');
                $configuration->setSQLLogger(new DbalSqlLogger($logger));
            }

            return DriverManager::getConnection($params, $configuration);
        };

        $this->container['orm.doctrine.entityManager'] = function (Container $c) {
            /** @var Connection $conn */
            $conn = $c->get('dbConnection');
            /** @var array $settings */
            $settings = $c->get('settings')->get('doctrine');

            $cache = $settings['cachingEnabled'] == true
                ? new ApcuCache()
                : new ArrayCache();

            Autoloader::register(
                $proxyDir = $settings['proxy']['dir'],
                $proxyNamespace = $settings['proxy']['namespace']
            );

            $configuration = new OrmConfiguration();

            $configuration->setProxyDir($proxyDir);
            $configuration->setProxyNamespace($proxyNamespace);
            $configuration->setAutoGenerateProxyClasses((int)$settings['proxy']['autogenerate']);

            $configuration->setMetadataDriverImpl(new PHPDriver($settings['metadata']['dir']));

            $configuration->setQueryCacheImpl($cache);
            $configuration->setMetadataCacheImpl($cache);

            return EntityManager::create($conn, $configuration);
        };

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
            /** @var HandlerRegistry $handlerRegistry */
            $handlerRegistry = $c->get('commandBus.handler.registry');

            return new ExecuteCommandPipe(
                new DefaultHandlerResolver($handlerRegistry),
                new EndPipe()
            );
        };

        $this->container['commandBus.handler.registry'] = function (Container $c) {
            return new HandlerRegistry($c);
        };

        $this->container['authManager'] = function (Container $c) {
            /** @var Connection $connection */
            $connection = $c->get('dbConnection');
            $strategy = new DbalAuthenticationStrategy($connection);

            return new AuthenticationManager($strategy);
        };
    }

    private function registerCommandHandlers()
    {
        $this->container['commandBus.handler.issueAccessToken'] = function (Container $c) {
            /** @var AuthenticationManager $authManager */
            $authManager = $c->get('authManager');
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new IssueAccessTokenHandler($authManager, $jwtAdapter);
        };

        $this->container['commandBus.handler.refreshAccessToken'] = function (Container $c) {
            /** @var AdapterInterface $jwtAdapter */
            $jwtAdapter = $c->get('jwtAdapter');
            return new RefreshAccessTokenHandler($jwtAdapter);
        };
    }

    private function registerRouteHandlers()
    {
        $this->container['actions.issueAccessToken'] = function (Container $c) {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $c->get('commandBus');
            return new IssueAccessTokenAction($commandBus);
        };

        $this->container['actions.refreshAccessToken'] = function (Container $c) {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $c->get('commandBus');
            return new RefreshAccessTokenAction($commandBus);
        };
    }
}
