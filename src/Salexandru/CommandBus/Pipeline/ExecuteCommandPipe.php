<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Exception\HandlerNotFoundException;
use Salexandru\CommandBus\Exception\UnexpectedValueException;
use Salexandru\CommandBus\Handler\Locator\LocatorInterface as HandlerLocator;

class ExecuteCommandPipe extends AbstractPipe
{

    private $handlerLocator;

    public function __construct(HandlerLocator $handlerLocator, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->handlerLocator = $handlerLocator;
    }

    public function receive(Command $cmd)
    {
        $commandHandler = $this->handlerLocator->locateHandlerFor($cmd);
        if (null === $commandHandler) {
            throw new HandlerNotFoundException('Could not locate a handler');
        }

        if (!is_callable($commandHandler)) {
            throw new UnexpectedValueException("Command handler must be a callable");
        }

        return call_user_func($commandHandler, $cmd);
    }
}
