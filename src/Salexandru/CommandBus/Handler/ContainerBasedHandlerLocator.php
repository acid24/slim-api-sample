<?php

namespace Salexandru\CommandBus\Handler;

use Interop\Container\ContainerInterface as Container;
use Salexandru\Command\CommandInterface as Command;

class ContainerBasedHandlerLocator implements HandlerLocatorInterface
{

    /**
     * @var array
     */
    private $map = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * ContainerBasedHandlerLocator constructor.
     *
     * @param Container $container
     * @param array $map
     */
    public function __construct(Container $container, array $map)
    {
        $this->container = $container;
        $this->map = $map;
    }

    /**
     * @param Command $cmd
     * @return object|null
     */
    public function locateHandlerFor(Command $cmd)
    {
        if (!isset($this->map[$class = get_class($cmd)])) {
            return null;
        }

        if (!$this->container->has($this->map[$class])) {
            return null;
        }

        return $this->container->get($this->map[$class]);
    }
}
