<?php

namespace Salexandru\EventBus;

use Mockery as m;
use Salexandru\EventBus\Handler\Registry as HandlerRegistry;

class EventBusTest extends \PHPUnit_Framework_TestCase
{

    private $handler1;
    private $handler2;
    private $handler3;
    private $handler4;
    private $callLog = [];

    protected function setUp()
    {
        $callLog =& $this->callLog;

        $this->handler1 = function () use (&$callLog) {
            $callLog[0] = 'Handler 1 called';
        };
        $this->handler2 = function () use (&$callLog) {
            $callLog[1] = 'Handler 2 called';
        };
        $this->handler3 = function () use (&$callLog) {
            $callLog[2] = 'Handler 3 called';
        };
        $this->handler4 = function (Event $e) use (&$callLog) {
            $callLog[3] = 'Handler 4 called';
            $e->stopPropagation();
        };

        unset($callLog);
    }

    protected function tearDown()
    {
        $this->callLog = [];
    }

    public function testReleaseEvents()
    {
        $e = new Event();

        $registry = m::mock(HandlerRegistry::class)
            ->shouldReceive('getHandlersFor')
            ->once()
            ->with($e)
            ->andReturn([$this->handler1, $this->handler2, $this->handler3])
            ->getMock();

        $eventBus = new EventBus($registry);
        $eventBus->collect($e);
        $eventBus->releaseEvents();

        $this->assertCount(3, $this->callLog);
        $this->assertEquals('Handler 1 called', $this->callLog[0]);
        $this->assertEquals('Handler 2 called', $this->callLog[1]);
        $this->assertEquals('Handler 3 called', $this->callLog[2]);
    }

    public function testReleaseEventsWithPropagationStopped()
    {
        $e = new Event();

        $registry = m::mock(HandlerRegistry::class)
            ->shouldReceive('getHandlersFor')
            ->once()
            ->with($e)
            ->andReturn([$this->handler1, $this->handler4, $this->handler3])
            ->getMock();

        $eventBus = new EventBus($registry);
        $eventBus->collect($e);
        $eventBus->releaseEvents();

        $this->assertCount(2, $this->callLog);
        $this->assertEquals('Handler 1 called', $this->callLog[0]);
        $this->assertEquals('Handler 4 called', $this->callLog[3]);
        $this->assertArrayNotHasKey(1, $this->callLog);
        $this->assertArrayNotHasKey(2, $this->callLog);
    }

    public function testNonCallableHandlerAreNotCalled()
    {
        $e = new Event();

        $registry = m::mock(HandlerRegistry::class)
            ->shouldReceive('getHandlersFor')
            ->once()
            ->with($e)
            ->andReturn([$this->handler1, new \stdClass(), $this->handler3])
            ->getMock();

        $eventBus = new EventBus($registry);
        $eventBus->collect($e);
        $eventBus->releaseEvents();

        $this->assertCount(2, $this->callLog);
        $this->assertEquals('Handler 1 called', $this->callLog[0]);
        $this->assertEquals('Handler 3 called', $this->callLog[2]);
        $this->assertArrayNotHasKey(1, $this->callLog);
    }
}
