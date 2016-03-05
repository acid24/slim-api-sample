<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\BaseCommand;
use Salexandru\Command\Exception\InvalidArgumentException;
use Salexandru\Command\LoggableInterface;

final class RefreshCommand extends BaseCommand implements LoggableInterface
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
     * @return string
     */
    public function getName()
    {
        return 'RefreshAccessToken';
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
