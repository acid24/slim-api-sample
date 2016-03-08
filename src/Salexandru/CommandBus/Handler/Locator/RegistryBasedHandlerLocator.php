<?php

namespace Salexandru\CommandBus\Handler\Locator;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\CommandBus\Handler\Registry\RegistryInterface as HandlerRegistry;

class RegistryBasedHandlerLocator implements LocatorInterface
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
    public function locateHandlerFor(Command $cmd)
    {
        return $this->registry->getHandler(get_class($cmd));
    }
}
