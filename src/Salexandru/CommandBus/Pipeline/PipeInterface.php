<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;

interface PipeInterface
{

    public function receive(Command $cmd);
}
