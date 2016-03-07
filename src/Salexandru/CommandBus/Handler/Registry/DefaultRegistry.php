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
        if (is_string($handler) && $this->container->has($handler)) {
            $this->storage[$key] = $handler;
        }
    }

    public function getHandler($key)
    {
        if (!isset($this->storage[$key])) {
            return null;
        }

        return $this->container->get($this->storage[$key]);
    }
}
