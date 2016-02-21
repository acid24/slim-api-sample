<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\AbstractCommand;
use Salexandru\Command\Exception\InvalidArgumentException;

final class IssueAccessTokenCommand extends AbstractCommand
{

    private $username;
    private $password;

    private function __construct()
    {
        // prevent instantiation
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    private function setUsername($username)
    {
        if (!is_string($username) || empty($username)) {
            throw new InvalidArgumentException('Username must be a non-empty string');
        }

        $this->username = $username;
    }

    private function setPassword($password)
    {
        if (!is_string($password) || empty($password)) {
            throw new InvalidArgumentException('Password must be a non-empty string');
        }

        $this->password = $password;
    }

    protected function getRequiredFields()
    {
        return ['username', 'password'];
    }
}
