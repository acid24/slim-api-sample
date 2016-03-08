<?php

namespace Salexandru\EventBus;

class Event
{

    private $propagate = true;

    public function stopPropagation()
    {
        $this->propagate = false;
    }

    public function isPropagationStopped()
    {
        return false === $this->propagate;
    }
}
