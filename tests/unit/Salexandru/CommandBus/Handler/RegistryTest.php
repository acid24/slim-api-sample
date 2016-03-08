<?php

namespace Salexandru\CommandBus\Handler;

use Mockery as m;
use Interop\Container\ContainerInterface as Container;

class RegistryTest extends \PHPUnit_Framework_TestCase
{

    public function testHandlerNotAddedToRegistryIfItDoesNotPointToServiceInsideContainer()
    {
        $container = m::mock(Container::class)
            ->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(false)
            ->getMock();

        $registry = new Registry($container);
        $registry->addHandlerFor($cmd = 'My\\TestCommand', $handler);

        $this->assertNull($registry->getHandlerFor($cmd));
    }

    public function testRegistryReturnsNullIfHandlerIsNotPresentInStorage()
    {
        $container = m::mock(Container::class)
            ->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(true)
            ->getMock();

        $registry = new Registry($container);
        $registry->addHandlerFor('My\\TestCommand', $handler);

        $this->assertNull($registry->getHandlerFor('My\\NonExistentCommand'));
    }

    public function testRetrieveHandler()
    {
        $h = function () {
            return true;
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

        $registry = new Registry($container);
        $registry->addHandlerFor($cmd = 'My\\TestCommand', $handler);

        $this->assertSame($h, $registry->getHandlerFor($cmd));
    }
}
