<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\AbstractCommand;
use Salexandru\Command\Exception\InvalidArgumentException;
use Salexandru\Command\LoggableInterface;

final class IssueCommand extends AbstractCommand implements LoggableInterface
{

    private $username;
    private $password;

    public function __construct($username, $password)
    {
        $this->setUsername($username);
        $this->setPassword($password);
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'IssueAccessToken';
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
}
