<?php

namespace Salexandru\CommandBus\Pipeline;

use Mockery as m;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Inflector\HandleInflector;
use Salexandru\CommandBus\Handler\Locator\LocatorInterface as HandlerLocator;
use Salexandru\CommandBus\Pipeline\PipeInterface as Pipe;
use Salexandru\CommandBus\Exception\HandlerNotFoundException;
use Salexandru\CommandBus\Exception\BadMethodCallException;
use Salexandru\Command\Handler\Result;

class ExecuteCommandPipeTest extends \PHPUnit_Framework_TestCase
{

    public function testLocatorNotFoundThrowsException()
    {
        $this->setExpectedException(HandlerNotFoundException::class);

        $cmd = m::mock(Command::class);
        $locator = m::mock(HandlerLocator::class)
            ->shouldReceive('locateHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturnNull()
            ->getMock();

        $pipe = new ExecuteCommandPipe($locator, new HandleInflector(), new EndPipe());
        $pipe->receive($cmd);
    }

    public function testNonExistentHandlerMethodThrowsException()
    {
        $this->setExpectedException(BadMethodCallException::class);

        $cmd = m::mock(Command::class);
        $locator = m::mock(HandlerLocator::class)
            ->shouldReceive('locateHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturn(new \stdClass())
            ->getMock();

        $pipe = new ExecuteCommandPipe($locator, new HandleInflector(), new EndPipe());
        $pipe->receive($cmd);
    }

    public function testCommandIsExecuted()
    {
        $cmd = m::mock(Command::class);

        $endPipe = m::mock(Pipe::class)
            ->shouldNotReceive('receive')
            ->getMock();

        $handler = m::mock('CommandHandler')
            ->shouldReceive('handle')
            ->once()
            ->with($cmd)
            ->andReturn(Result::success())
            ->getMock();

        $locator = m::mock(HandlerLocator::class)
            ->shouldReceive('locateHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturn($handler)
            ->getMock();

        $pipe = new ExecuteCommandPipe($locator, new HandleInflector(), $endPipe);
        $result = $pipe->receive($cmd);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
    }
}
