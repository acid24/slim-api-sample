<?php

namespace Salexandru\Api\Server\Bootstrap;

use Salexandru\Command\AccessToken\IssueCommand as IssueAccessTokenCommand;
use Salexandru\Command\AccessToken\RefreshCommand as RefreshAccessTokenCommand;

final class CommandToHandlerMap
{

    /**
     * Holds the mapping for commands that are used by API and their
     * associated handlers. Key names are fully qualified command class
     * names. Values are the name of the keys where the handlers are stored
     * inside the dependency injection container
     * @var array
     */
    private static $map = [
        IssueAccessTokenCommand::class => 'commandBus.handler.issueAccessToken',
        RefreshAccessTokenCommand::class => 'commandBus.handler.refreshAccessToken'
    ];

    private function __construct()
    {
        // prevent instantiation
    }

    public static function getMap()
    {
        return self::$map;
    }
}
