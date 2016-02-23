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
        $cmdClass = get_class($cmd);

        $this->logger->info(sprintf('Received command %s', $cmdClass));

        /** @var Result $result */
        $result = $this->nextPipe->receive($cmd);

        if ($result->isError()) {
            $this->logger->error(sprintf(
                'Error executing command %s; error message was: "%s"',
                $cmdClass,
                $result->getLastErrorMessage()
            ));
        } else {
            $this->logger->info(sprintf('Command %s executed successfully', $cmdClass));
        }

        return $result;
    }
}
