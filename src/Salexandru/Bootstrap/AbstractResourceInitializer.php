<?php

namespace Salexandru\Bootstrap;

use Interop\Container\ContainerInterface as Container;

abstract class AbstractResourceInitializer implements ResourceInitializerInterface
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
