<?php

namespace Salexandru\CommandBus\Handler;

use Interop\Container\ContainerInterface as Container;

class Registry
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * Registry constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $command The command's fully qualified class name
     * @param mixed $handler The location of the handler inside the container
     */
    public function addHandlerFor($command, $handler)
    {
        if ($this->container->has($handler)) {
            $this->storage[$command] = $handler;
        }
    }

    /**
     * @param string $command The command's fully qualified class name
     * @return mixed|null
     */
    public function getHandlerFor($command)
    {
        if (!isset($this->storage[$command])) {
            return null;
        }

        return $this->container->get($this->storage[$command]);
    }
}
