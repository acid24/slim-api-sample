<?php

namespace Salexandru\CommandBus\Handler\Resolver;

use Mockery as m;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Registry as HandlerRegistry;

class DefaultHandlerResolverTest extends \PHPUnit_Framework_TestCase
{

    public function testLocateHandler()
    {
        $expectedHandler = function () {
            // do something
        };

        $cmd = m::mock(Command::class);

        $registry = m::mock(HandlerRegistry::class)
            ->shouldReceive('getHandlerFor')
            ->once()
            ->with($cmd->mockery_getName())
            ->andReturn($expectedHandler)
            ->getMock();

        $locator = new DefaultHandlerResolver($registry);
        $actualHandler = $locator->resolveHandlerFor($cmd);

        $this->assertSame($expectedHandler, $actualHandler);
    }
}
