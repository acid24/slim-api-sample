<?php

namespace Salexandru\EventBus\Handler;

use Interop\Container\ContainerInterface as Container;

class Registry
{

    private $container;
    private $storage = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addHandlerFor($event, $handler)
    {
        if ($this->container->has($handler)) {
            $this->storage[$event][] = $handler;
        }
    }

    public function getHandlersFor($event)
    {
        if (!isset($this->storage[$event])) {
            return [];
        }

        $handlers = [];
        foreach ($this->storage[$event] as $handler) {
            if (null !== ($h = $this->resolveHandler($handler))) {
                $handlers[] = $h;
            }
        }

        return $handlers;
    }

    private function resolveHandler($name)
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        return null;
    }
}
