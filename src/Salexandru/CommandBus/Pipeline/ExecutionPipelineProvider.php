<?php

namespace Salexandru\CommandBus\Pipeline;

use Interop\Container\ContainerInterface as Container;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\LoggableInterface;
use Salexandru\Command\TransactionalInterface;
use Salexandru\Command\TriggersEventsInterface;

class ExecutionPipelineProvider
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getExecutionPipelineFor(Command $cmd)
    {
        $pipeline = $this->container->get('commandBus.pipe.executeCommand');

        if ($cmd instanceof TransactionalInterface) {
            $pipeline = new TransactionPipe($this->container->get('transactionManager'), $pipeline);
        }

        if ($cmd instanceof TriggersEventsInterface) {
            $pipeline = new ReleaseEventsPipe($this->container->get('eventBus'), $pipeline);
        }

        if ($cmd instanceof LoggableInterface) {
            $pipeline = new LoggingPipe($this->container->get('logger.app'), $pipeline);
        }

        return $pipeline;
    }
}
