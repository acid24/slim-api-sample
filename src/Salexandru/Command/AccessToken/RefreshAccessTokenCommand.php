<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\AbstractCommand;
use Salexandru\Command\Exception\InvalidArgumentException;
use Salexandru\Command\LoggableInterface;

final class RefreshAccessTokenCommand extends AbstractCommand implements LoggableInterface
{

    private $currentAccessToken;

    public function __construct($currentAccessToken)
    {
        $this->setCurrentAccessToken($currentAccessToken);
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
}
