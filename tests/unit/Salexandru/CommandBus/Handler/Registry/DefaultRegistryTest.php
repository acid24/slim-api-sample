<?php

namespace Salexandru\CommandBus\Handler\Registry;

use Mockery as m;
use Interop\Container\ContainerInterface as Container;
use Salexandru\Command\CommandInterface as Command;

class DefaultRegistryTest extends \PHPUnit_Framework_TestCase
{

    public function testRetrieveHandlerBypassingContainer()
    {
        $handler = new \stdClass();
        $cmd = m::mock(Command::class);

        $container = m::mock(Container::class);
        $container->shouldNotReceive('has');
        $container->shouldNotReceive('get');

        $registry = new DefaultRegistry($container);
        $registry->addHandlerFor($cmd, $handler);

        $this->assertSame($handler, $registry->getHandlerFor($cmd));
    }

    public function testRegistryReturnsNullIfHandlerNotPresentInStorage()
    {
        $cmd = m::mock(Command::class);

        $container = m::mock(Container::class);
        $container->shouldNotReceive('has');
        $container->shouldNotReceive('get');

        $registry = new DefaultRegistry($container);

        $this->assertNull($registry->getHandlerFor($cmd));
    }

    public function testRegistryReturnsNullIfHandlerNotPresentInContainer()
    {
        $handlerKey = 'handler.test';
        $cmd = m::mock(Command::class);

        $container = m::mock(Container::class);
        $container->shouldReceive('has')
            ->with($handlerKey)
            ->once()
            ->andReturn(false);

        $registry = new DefaultRegistry($container);
        $registry->addHandlerFor($cmd, $handlerKey);

        $this->assertNull($registry->getHandlerFor($cmd));
    }

    public function testRetrieveHandlerUsingContainer()
    {
        $handler = new \stdClass();
        $handlerKey = 'handler.test';
        $cmd = m::mock(Command::class);

        $container = m::mock(Container::class);
        $container->shouldReceive('has')
            ->with($handlerKey)
            ->once()
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with($handlerKey)
            ->once()
            ->andReturn($handler);

        $registry = new DefaultRegistry($container);
        $registry->addHandlerFor($cmd, $handlerKey);

        $this->assertSame($handler, $registry->getHandlerFor($cmd));
    }
}
