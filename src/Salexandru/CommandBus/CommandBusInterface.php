<?php

namespace Salexandru\CommandBus;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\Handler\Result;

interface CommandBusInterface
{

    /**
     * @param Command $cmd
     * @return Result
     */
    public function handle(Command $cmd);
}
