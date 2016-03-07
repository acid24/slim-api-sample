<?php

namespace Salexandru\CommandBus\Handler\Registry;

interface RegistryInterface
{

    public function addHandlerFor($cmd, $handler);
    public function getHandlerFor($cmd);
}
