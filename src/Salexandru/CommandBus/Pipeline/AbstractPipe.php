<?php

namespace Salexandru\CommandBus\Pipeline;

abstract class AbstractPipe implements PipeInterface
{

    protected $nextPipe;

    public function __construct(PipeInterface $nextPipe)
    {
        $this->nextPipe = $nextPipe;
    }
}
