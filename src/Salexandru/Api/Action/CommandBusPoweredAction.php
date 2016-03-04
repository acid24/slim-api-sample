<?php

namespace Salexandru\Api\Action;

use Salexandru\CommandBus\CommandBusInterface as CommandBus;

abstract class CommandBusPoweredAction extends BaseAction
{

    /**
     * @var CommandBus
     */
    protected $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
