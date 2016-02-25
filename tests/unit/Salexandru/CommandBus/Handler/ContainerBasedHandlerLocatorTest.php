<?php

namespace Salexandru\CommandBus\Handler;

use \Mockery as m;
use Interop\Container\ContainerInterface as Container;
use Salexandru\Command\CommandInterface as Command;

class ContainerBasedHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{

    public function testMissingMapEntryForCommandReturnsNull()
    {
        $container = m::mock(Container::class);
        $container->shouldNotReceive('get');

        $cmd = m::mock(Command::class);

        $handler = new ContainerBasedHandlerLocator($container, []);
        $result = $handler->locateHandlerFor($cmd);

        $this->assertNull($result);
    }

    public function testMissingServiceDefinitionInContainerReturnsNull()
    {
        $cmd = m::mock(Command::class);
        $map = [get_class($cmd) => 'handler.service.name'];

        $container = m::mock(Container::class);
        $container->shouldNotReceive('get');
        $container->shouldReceive('has')
            ->once()
            ->with('handler.service.name')
            ->andReturn(false);

        $handler = new ContainerBasedHandlerLocator($container, $map);
        $result = $handler->locateHandlerFor($cmd);

        $this->assertNull($result);
    }

    public function testHandlerCanBeLocated()
    {
        $cmd = m::mock(Command::class);
        $map = [get_class($cmd) => $s = 'handler.service.name'];

        $handler = function () {
            return;
        };

        $container = m::mock(Container::class);
        $container->shouldReceive('has')
            ->once()
            ->with($s)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with($s)
            ->andReturn($handler);

        $locator = new ContainerBasedHandlerLocator($container, $map);
        $h = $locator->locateHandlerFor($cmd);

        $this->assertSame($handler, $h);
    }
}
