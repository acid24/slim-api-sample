<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;

class EndPipe implements PipeInterface
{

    public function receive(Command $cmd)
    {
        // end pipe does nothing
    }
}
