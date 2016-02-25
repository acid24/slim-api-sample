<?php

namespace Salexandru\CommandBus\Pipeline;

use \Mockery as m;
use Interop\Container\ContainerInterface as Container;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Pipeline\PipeInterface as Pipe;

class ExecutionPipelineProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDefaultExecutionPipeline()
    {
        $cmd = m::mock(Command::class);
        $pipe = m::mock(Pipe::class);

        $container = m::mock(Container::class)
            ->shouldReceive('get')
            ->once()
            ->with('pipe.executeCommand')
            ->andReturn($pipe)
            ->getMock();

        $provider = new ExecutionPipelineProvider($container);
        $p = $provider->getExecutionPipelineFor($cmd);

        $this->assertSame($pipe, $p);
    }
}
