<?php

namespace Salexandru\CommandBus\Pipeline;

use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\Handler\Result;

class TransactionPipe extends AbstractPipe
{

    private $transactionManager;

    public function __construct(TransactionManager $transactionManager, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->transactionManager = $transactionManager;
    }

    public function receive(Command $cmd)
    {
        $this->transactionManager->startTransaction();

        /** @var Result $result */
        $result = $this->nextPipe->receive($cmd);

        if ($result->isSuccess()) {
            $this->transactionManager->commitTransaction();
        } else {
            $this->transactionManager->rollbackTransaction();
        }

        return $result;
    }
}
