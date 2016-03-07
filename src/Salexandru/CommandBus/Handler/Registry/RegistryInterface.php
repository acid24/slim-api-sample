<?php

namespace Salexandru\CommandBus\Handler\Registry;

use Salexandru\Command\CommandInterface as Command;

interface RegistryInterface
{

    public function addHandlerFor(Command $cmd, $handler);
    public function getHandlerFor(Command $cmd);
}
