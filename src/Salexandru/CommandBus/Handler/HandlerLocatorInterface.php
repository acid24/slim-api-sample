<?php

namespace Salexandru\CommandBus\Handler;

use Salexandru\Command\CommandInterface as Command;

interface HandlerLocatorInterface
{

    /**
     * @param Command $cmd
     * @return CommandHandler|null
     */
    public function locateHandlerFor(Command $cmd);
}
