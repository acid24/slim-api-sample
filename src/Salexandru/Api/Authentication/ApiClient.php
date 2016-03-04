<?php

namespace Salexandru\Api\Authentication;

use Salexandru\Authentication\SubjectInterface;
use Salexandru\Authentication\UsernameAndPasswordCredentials;

class ApiClient implements SubjectInterface
{

    private $username;
    private $password;
    private $attributes = [];

    public function __construct($username, $password, array $attributes = null)
    {
        $this->username = $username;
        $this->password = $password;
        if (null !== $attributes) {
            $this->setAttributes($attributes);
        }
    }

    public function getCredentials()
    {
        return new UsernameAndPasswordCredentials($this->username, $this->password);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
