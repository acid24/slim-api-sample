<?php

namespace Salexandru\CommandBus\Handler\Inflector;

use Salexandru\Command\CommandInterface as Command;

interface MethodNameInflectorInterface
{

    public function inflect(Command $cmd, $commandHandler);
}
