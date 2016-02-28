<?php

namespace Salexandru\CommandBus\Pipeline;

use Psr\Log\LoggerInterface as Logger;
use Salexandru\Command\CommandInterface as Command;
use Salexandru\Command\Handler\Result;

class LoggingPipe extends AbstractPipe
{

    private $logger;

    public function __construct(Logger $logger, PipeInterface $nextPipe)
    {
        parent::__construct($nextPipe);
        $this->logger = $logger;
    }

    public function receive(Command $cmd)
    {
        $this->logger->info('Received command {cmd}', ['cmd' => $cmd->getName()]);

        /** @var Result $result */
        $result = $this->nextPipe->receive($cmd);

        if ($result->isError()) {
            $this->logger->error(
                'Error executing {cmd} command; error message was: {err}',
                ['cmd' => $cmd->getName(), 'err' => $result->getLastErrorMessage()]
            );
        } else {
            $this->logger->info('Command {cmd} executed successfully', ['cmd' => $cmd->getName()]);
        }

        return $result;
    }
}
