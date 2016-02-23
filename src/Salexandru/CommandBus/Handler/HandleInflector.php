<?php

namespace Salexandru\CommandBus\Handler;

use Salexandru\Command\CommandInterface as Command;

class HandleInflector implements MethodNameInflectorInterface
{

    public function inflect(Command $cmd, $commandHandler)
    {
        return 'handle';
    }
}
