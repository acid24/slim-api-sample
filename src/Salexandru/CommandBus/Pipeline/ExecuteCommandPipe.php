<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Exception\HandlerNotFoundException;
use Salexandru\CommandBus\Exception\UnexpectedValueException;
use Salexandru\CommandBus\Handler\Resolver\ResolverInterface as HandlerResolver;

class ExecuteCommandPipe extends AbstractPipe
{

    private $handlerResolver;

    public function __construct(HandlerResolver $handlerResolver, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->handlerResolver = $handlerResolver;
    }

    public function receive(Command $cmd)
    {
        $commandHandler = $this->handlerResolver->resolveHandlerFor($cmd);
        if (null === $commandHandler) {
            throw new HandlerNotFoundException('Could not locate a handler');
        }

        if (!is_callable($commandHandler)) {
            throw new UnexpectedValueException("Command handler must be a callable");
        }

        return call_user_func($commandHandler, $cmd);
    }
}
