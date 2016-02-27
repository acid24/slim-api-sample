<?php

namespace Salexandru\Api\Actions\AccessToken;

use Salexandru\Api\Actions\AbstractCommandBusPoweredAction;
use Salexandru\Api\Server;
use Salexandru\Command\AccessToken\IssueCommand as IssueAccessTokenCommand;
use Salexandru\Command\Exception\ExceptionInterface as CommandException;
use Salexandru\Command\Handler\Result;

class IssueAction extends AbstractCommandBusPoweredAction
{

    protected function performAction()
    {
        /** @var array $input */
        $input = $this->request->getParsedBody();

        if (!isset($input['username'])) {
            return $this->badRequest(Server::ERROR_UNEXPECTED_INPUT, 'Bad input; missing username');
        }

        if (!isset($input['password'])) {
            return $this->badRequest(Server::ERROR_UNEXPECTED_INPUT, 'Bad input; missing password');
        }

        try {
            $cmd = new IssueAccessTokenCommand($input['username'], $input['password']);
        } catch (CommandException $e) {
            return $this->badRequest(Server::ERROR_UNEXPECTED_INPUT, 'Bad input; invalid username/password');
        }

        /** @var Result $result */
        $result = $this->commandBus->handle($cmd);

        if ($result->isSuccess()) {
            return $this->buildResponse($this->response, $result->getPayload());
        }

        switch (true) {
            case $result->isInvalidUserCredentialsError():
                return $this->badRequest($result->getErrorCode(), $result->getFirstErrorMessage());
            case $result->isAccessTokenGenerationError():
                return $this->serverError($result->getErrorCode(), $result->getFirstErrorMessage());
            default:
                return $this->serverError(Result::ERROR_GENERIC);
        }
    }
}
