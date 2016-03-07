<?php

namespace Salexandru\Api\Action\AccessToken;

use Salexandru\Api\Action\CommandBusAwareAction;
use Salexandru\Api\Server;
use Salexandru\Command\AccessToken\RefreshCommand as RefreshAccessTokenCommand;
use Salexandru\Command\Exception\ExceptionInterface as CommandException;
use Salexandru\Command\Handler\Result;

class RefreshAction extends CommandBusAwareAction
{

    protected function performAction()
    {
        /** @var array $input */
        $input = $this->request->getParsedBody();

        if (!isset($input['currentToken'])) {
            return $this->badRequest(Server::ERROR_UNEXPECTED_INPUT, 'Bad input; missing current token');
        }

        try {
            $cmd = new RefreshAccessTokenCommand($input['currentToken']);
        } catch (CommandException $e) {
            return $this->badRequest(Server::ERROR_UNEXPECTED_INPUT, 'Bad input; invalid jwt token');
        }

        /** @var Result $result */
        $result = $this->commandBus->handle($cmd);

        if ($result->isSuccess()) {
            return $this->buildResponse($this->response, $result->getPayload());
        }

        switch (true) {
            case $result->isInvalidAccessTokenError():
                return $this->badRequest($result->getErrorCode(), $result->getFirstErrorMessage());
            case $result->isAccessTokenGenerationError():
                return $this->serverError($result->getErrorCode(), $result->getFirstErrorMessage());
            default:
                return $this->serverError(Result::ERROR_GENERIC);
        }
    }
}
