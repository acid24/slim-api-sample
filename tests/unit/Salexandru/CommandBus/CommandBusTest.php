<?php

namespace Salexandru\CommandBus;

use \Mockery as m;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Pipeline\ExecutionPipelineProvider;
use Salexandru\CommandBus\Exception\IllegalStateException;
use Salexandru\CommandBus\Pipeline\PipeInterface as Pipe;

class CommandBusTest extends \PHPUnit_Framework_TestCase
{

    public function testExecutingCommandInTheMiddleOfAnotherCommandExecutionThrowsException()
    {
        $this->setExpectedException(IllegalStateException::class);

        $cmd = m::mock(Command::class);
        $pipelineProvider = m::mock(ExecutionPipelineProvider::class);
        $pipe1 = m::mock(Pipe::class);
        $pipe2 = m::mock(Pipe::class);

        $commandBus = new CommandBus($pipelineProvider);

        $pipe1->shouldReceive('receive')
            ->once()
            ->with($cmd)
            ->andReturnUsing(function (Command $cmd) use ($commandBus) {
                return $commandBus->handle($cmd);
            });
        $pipe2->shouldReceive('receive')
            ->once()
            ->with($cmd)
            ->andReturnNull();

        $pipelineProvider->shouldReceive('getExecutionPipelineFor')
            ->zeroOrMoreTimes()
            ->with($cmd)
            ->andReturn($pipe1, $pipe2);

        $commandBus->handle($cmd);
    }

    public function testCommandHandling()
    {
        $cmd = m::mock(Command::class);
        $pipelineProvider = m::mock(ExecutionPipelineProvider::class);
        $pipe = m::mock(Pipe::class);

        $commandBus = new CommandBus($pipelineProvider);

        $pipe->shouldReceive('receive')
            ->once()
            ->with($cmd)
            ->andReturnNull();

        $pipelineProvider->shouldReceive('getExecutionPipelineFor')
            ->zeroOrMoreTimes()
            ->with($cmd)
            ->andReturn($pipe);

        $commandBus->handle($cmd);
    }
}
