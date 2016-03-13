<?php

namespace Salexandru\CommandBus\Handler\Resolver;

use Salexandru\Command\CommandInterface as Command;

interface ResolverInterface
{

    /**
     * @param Command $cmd
     * @return callable|null
     */
    public function resolveHandlerFor(Command $cmd);
}
