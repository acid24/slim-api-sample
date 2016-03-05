<?php

namespace Salexandru\Command;

abstract class BaseCommand implements CommandInterface
{

    /**
     * Holds information that is available at the time the command is created
     * and may be of use when the command is handled
     *
     * @var Context
     */
    private $context;

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function hasContext()
    {
        return null !== $this->context;
    }
}
