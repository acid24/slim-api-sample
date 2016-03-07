<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Exception\BadMethodCallException;
use Salexandru\CommandBus\Exception\HandlerNotFoundException;
use Salexandru\CommandBus\Handler\Locator\LocatorInterface as HandlerLocator;
use Salexandru\CommandBus\Handler\Inflector\MethodNameInflectorInterface as MethodNameInflector;

class ExecuteCommandPipe extends AbstractPipe
{

    private $handlerLocator;
    private $inflector;

    public function __construct(HandlerLocator $handlerLocator, MethodNameInflector $inflector, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->handlerLocator = $handlerLocator;
        $this->inflector = $inflector;
    }

    public function receive(Command $cmd)
    {
        $commandHandler = $this->handlerLocator->locateHandlerFor($cmd);
        if (null === $commandHandler) {
            throw new HandlerNotFoundException('Could not locate a handler');
        }

        $method = $this->inflector->inflect($cmd, $commandHandler);
        if (!is_callable([$commandHandler, $method])) {
            throw new BadMethodCallException("Method $method does not exist on handler");
        }

        return $commandHandler->$method($cmd);
    }
}
