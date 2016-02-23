<?php

namespace Salexandru\CommandBus;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\Handler\Result;
use Salexandru\CommandBus\Exception\IllegalStateException;
use Salexandru\CommandBus\Pipeline\ExecutionPipelineProvider;
use Salexandru\CommandBus\Pipeline\PipeInterface as Pipe;

class CommandBus implements CommandBusInterface
{

    private $executionPipelineProvider;
    private $executing = false;

    public function __construct(ExecutionPipelineProvider $executionPipelineProvider)
    {
        $this->executionPipelineProvider = $executionPipelineProvider;
    }

    public function handle(Command $cmd)
    {
        if ($this->executing) {
            throw new IllegalStateException('A command cannot be executed while another command is being executed');
        }

        $this->executing = true;

        /** @var Pipe $pipeline */
        $pipeline = $this->executionPipelineProvider->getExecutionPipelineFor($cmd);
        /** @var Result $result */
        $result = $pipeline->receive($cmd);

        $this->executing = false;

        return $result;
    }
}
