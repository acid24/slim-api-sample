<?php

namespace Salexandru\EventBus\Handler;

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
        $registry->addHandlerFor($event = 'My\\TestEvent', $handler);

        $this->assertEquals([], $registry->getHandlersFor($event));
    }

    public function testRegistryReturnsEmptyArrayIfNoHandlersFoundInStorage()
    {
        $container = m::mock(Container::class)
            ->shouldReceive('has')
            ->once()
            ->with($handler = 'handler.test')
            ->andReturn(true)
            ->getMock();

        $registry = new Registry($container);
        $registry->addHandlerFor('My\\TestEvent', $handler);

        $this->assertEquals([], $registry->getHandlersFor('My\\NonExistentEvent'));
    }

    public function testRetrieveHandlers()
    {
        $h1 = function () {
            return true;
        };
        $h2 = function () {
            return true;
        };

        $container = m::mock(Container::class);

        $container->shouldReceive('has')
            ->with($handler1 = 'handler.test1')
            ->andReturn(true);
        $container->shouldReceive('has')
            ->with($handler2 = 'handler.test2')
            ->andReturn(true);

        $container->shouldReceive('get')
            ->with($handler1)
            ->andReturn($h1);
        $container->shouldReceive('get')
            ->with($handler2)
            ->andReturn($h2);

        $registry = new Registry($container);
        $registry->addHandlerFor($event1 = 'My\\TestEvent', $handler1);
        $registry->addHandlerFor($event2 = 'My\\AnotherTestEvent', $handler2);

        $this->assertEquals([$h1], $registry->getHandlersFor($event1));
        $this->assertEquals([$h2], $registry->getHandlersFor($event2));
    }
}
