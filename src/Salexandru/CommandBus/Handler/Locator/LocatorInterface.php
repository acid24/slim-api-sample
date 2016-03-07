<?php

namespace Salexandru\CommandBus\Handler\Locator;

use Salexandru\Command\CommandInterface as Command;

interface LocatorInterface
{

    /**
     * @param Command $cmd
     * @return object|null
     */
    public function locateHandlerFor(Command $cmd);
}
