<?php

namespace Salexandru\CommandBus\Pipeline;

use Mockery as m;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Resolver\ResolverInterface as HandlerResolver;
use Salexandru\CommandBus\Pipeline\PipeInterface as Pipe;
use Salexandru\CommandBus\Exception\HandlerNotFoundException;
use Salexandru\CommandBus\Exception\UnexpectedValueException;
use Salexandru\Command\Handler\Result;

class ExecuteCommandPipeTest extends \PHPUnit_Framework_TestCase
{

    public function testLocatorNotFoundThrowsException()
    {
        $this->setExpectedException(HandlerNotFoundException::class);

        $cmd = m::mock(Command::class);
        $resolver = m::mock(HandlerResolver::class)
            ->shouldReceive('resolveHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturnNull()
            ->getMock();

        $pipe = new ExecuteCommandPipe($resolver, new EndPipe());
        $pipe->receive($cmd);
    }

    public function testNonExistentHandlerMethodThrowsException()
    {
        $this->setExpectedException(UnexpectedValueException::class);

        $cmd = m::mock(Command::class);
        $resolver = m::mock(HandlerResolver::class)
            ->shouldReceive('resolveHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturn(new \stdClass())
            ->getMock();

        $pipe = new ExecuteCommandPipe($resolver, new EndPipe());
        $pipe->receive($cmd);
    }

    public function testCommandIsExecuted()
    {
        $cmd = m::mock(Command::class);

        $endPipe = m::mock(Pipe::class)
            ->shouldNotReceive('receive')
            ->getMock();

        $handler = function (Command $cmd) {
            return Result::success();
        };

        $resolver = m::mock(HandlerResolver::class)
            ->shouldReceive('resolveHandlerFor')
            ->once()
            ->with($cmd)
            ->andReturn($handler)
            ->getMock();

        $pipe = new ExecuteCommandPipe($resolver, $endPipe);
        $result = $pipe->receive($cmd);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
    }
}
