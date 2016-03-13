<?php

namespace Salexandru\CommandBus\Handler\Resolver;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Registry as HandlerRegistry;

class DefaultHandlerResolver implements ResolverInterface
{

    private $registry;

    public function __construct(HandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Command $cmd
     * @return callable|null
     */
    public function resolveHandlerFor(Command $cmd)
    {
        return $this->registry->getHandlerFor(get_class($cmd));
    }
}
