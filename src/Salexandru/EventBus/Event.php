<?php

namespace Salexandru\EventBus;

class Event
{

    private $name;
    private $propagate = true;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function stopPropagation()
    {
        $this->propagate = false;
    }

    public function isPropagationStopped()
    {
        return false === $this->propagate;
    }
}
