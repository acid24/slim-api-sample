<?php

namespace Salexandru\CommandBus\Handler\Registry;

use Salexandru\Command\CommandInterface as Command;
use Interop\Container\ContainerInterface as Container;

class DefaultRegistry implements RegistryInterface
{

    private $container;
    private $storage = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addHandlerFor($cmd, $handler)
    {
        $this->storage[$cmd] = $handler;
    }

    public function getHandlerFor($cmd)
    {
        if (!isset($this->storage[$cmd])) {
            return null;
        }

        $handler = $this->storage[$cmd];

        if (is_string($handler)) {
            if (!$this->container->has($handler)) {
                return null;
            }

            $handler = $this->container->get($handler);
        }

        return $handler;
    }
}
