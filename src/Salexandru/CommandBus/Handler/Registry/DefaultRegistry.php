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

    public function addHandlerFor(Command $cmd, $handler)
    {
        $this->storage[get_class($cmd)] = $handler;
    }

    public function getHandlerFor(Command $cmd)
    {
        $class = get_class($cmd);

        if (!isset($this->storage[$class])) {
            return null;
        }

        $handler = $this->storage[$class];

        if (is_string($handler)) {
            if (!$this->container->has($handler)) {
                return null;
            }

            $handler = $this->container->get($handler);
        }

        return $handler;
    }

    public function clear()
    {
        $this->storage = [];
    }
}
