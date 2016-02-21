<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\AbstractCommand;
use Salexandru\Command\Exception\InvalidArgumentException;

final class RefreshAccessTokenCommand extends AbstractCommand
{

    private $currentAccessToken;

    private function __construct()
    {
        // prevent instantiation
    }

    /**
     * @return mixed
     */
    public function getCurrentAccessToken()
    {
        return $this->currentAccessToken;
    }

    /**
     * @param mixed $currentAccessToken
     */
    private function setCurrentAccessToken($currentAccessToken)
    {
        if (!is_string($currentAccessToken) || empty($currentAccessToken)) {
            throw new InvalidArgumentException('Access token must be a non-empty string');
        }

        $this->currentAccessToken = $currentAccessToken;
    }

    protected function getRequiredFields()
    {
        return ['currentAccessToken'];
    }
}
