<?php

namespace Salexandru\CommandBus\Handler\Locator;

use Mockery as m;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Registry\RegistryInterface as HandlerRegistry;

class RegistryBasedHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{

    public function testLocateHandler()
    {
        $expectedHandler = function () {
            // do something
        };

        $cmd = m::mock(Command::class);

        $registry = m::mock(HandlerRegistry::class)
            ->shouldReceive('getHandler')
            ->once()
            ->with($cmd->mockery_getName())
            ->andReturn($expectedHandler)
            ->getMock();

        $locator = new RegistryBasedHandlerLocator($registry);
        $actualHandler = $locator->locateHandlerFor($cmd);

        $this->assertSame($expectedHandler, $actualHandler);
    }
}
