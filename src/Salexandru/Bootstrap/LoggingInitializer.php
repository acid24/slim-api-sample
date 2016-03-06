<?php

namespace Salexandru\Bootstrap;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Slim\Collection;
use Slim\Http\Environment;

class LoggingInitializer extends AbstractResourceInitializer
{

    private $map = [
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

    /**
     * @return array
     */
    public function getOptions()
    {
        /** @var Environment $environment */
        $environment = $this->container->get('environment');
        $appEnv = $environment->get('APPLICATION_ENV', 'production');

        $level = 'debug';
        if ($appEnv == 'production') {
            $level = 'error';
        }

        $defaults = [
            'path' => sys_get_temp_dir() . '/app.log',
            'level' => $level
        ];

        /** @var Collection $settings */
        $settings = $this->container->get('settings');

        return array_merge($defaults, $settings->get('logging'));
    }

    /**
     * @return void
     */
    public function run()
    {
        $options = $this->getOptions();

        $outputFormat = "[%channel%.%level_name%] %datetime%: %message%\n";
        $formatter = new LineFormatter($outputFormat, \DateTime::RFC822);

        $streamHandler = new StreamHandler($options['path'], $this->map[$options['level']]);
        $streamHandler->setFormatter($formatter);
        $processor = new PsrLogMessageProcessor();

        $this->container['logger.app'] = function () use ($streamHandler, $processor) {
            new Logger('APP', [$streamHandler], [$processor]);
        };
        $this->container['logger.http'] = function () use ($streamHandler, $processor) {
            new Logger('HTTP', [$streamHandler], [$processor]);
        };
        $this->container['logger.sql'] = function () use ($streamHandler, $processor) {
            new Logger('SQL', [$streamHandler], [$processor]);
        };
    }
}
