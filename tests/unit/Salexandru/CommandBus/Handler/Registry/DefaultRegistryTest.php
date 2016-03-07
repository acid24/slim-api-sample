<?php

namespace Salexandru\CommandBus\Handler\Registry;

use Mockery as m;
use Interop\Container\ContainerInterface as Container;

class DefaultRegistryTest extends \PHPUnit_Framework_TestCase
{

    public function testHandlerNotAddedToRegistryIfItDoesNotPointToServiceInsideContainer()
    {
        $container = m::mock(Container::class)
            ->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(false)
            ->getMock();

        $registry = new DefaultRegistry($container);
        $registry->addHandler('test', $handler);

        $this->assertNull($registry->getHandler('test'));
    }

    public function testRegistryReturnsNullIfHandlerIsNotPresentInStorage()
    {
        $container = m::mock(Container::class)
            ->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(true)
            ->getMock();

        $registry = new DefaultRegistry($container);
        $registry->addHandler('test', $handler);

        $this->assertNull($registry->getHandler('non-existent'));
    }

    public function testRetrieveHandler()
    {
        $h = function () {
            // handle something
        };

        $container = m::mock(Container::class);
        $container->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with($handler)
            ->andReturn($h);

        $registry = new DefaultRegistry($container);
        $registry->addHandler('test', $handler);

        $this->assertSame($h, $registry->getHandler('test'));
    }
}
