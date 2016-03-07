<?php

namespace Salexandru\CommandBus\Handler\Registry;

interface RegistryInterface
{

    public function addHandler($key, $handler);
    public function getHandler($key);
}
