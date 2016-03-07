<?php

namespace Salexandru\CommandBus\Handler\Registry;

use Interop\Container\ContainerInterface as Container;

class DefaultRegistry implements RegistryInterface
{

    private $container;
    private $storage = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addHandler($key, $handler)
    {
        $this->storage[$key] = $handler;
    }

    public function getHandler($key)
    {
        if (!isset($this->storage[$key])) {
            return null;
        }

        $handler = $this->storage[$key];

        if (is_string($handler)) {
            if (!$this->container->has($handler)) {
                return null;
            }

            $handler = $this->container->get($handler);
        }

        return $handler;
    }
}
