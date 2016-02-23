<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\Handler\Result;

class ReleaseEventsPipe extends AbstractPipe
{

    private $eventBus;

    public function __construct(EventBus $eventBus, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->eventBus = $eventBus;
    }

    public function receive(Command $cmd)
    {
        /** @var Result $result */
        $result = $this->nextPipe->receive($cmd);

        if ($result->isSuccess()) {
            $this->eventBus->releaseEvents();
        }

        return $result;
    }
}
