<?php

namespace Salexandru\Authentication;

class AuthenticationManager
{

    private $authenticationStrategy;

    public function __construct(AuthenticationStrategyInterface $authenticationStrategy)
    {
        $this->authenticationStrategy = $authenticationStrategy;
    }

    public function authenticate(SubjectInterface $subject)
    {
        return $this->authenticationStrategy->authenticate($subject);
    }
}
