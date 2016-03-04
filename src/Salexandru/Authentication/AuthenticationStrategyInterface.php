<?php

namespace Salexandru\Authentication;

interface AuthenticationStrategyInterface
{

    /**
     * @param SubjectInterface $subject
     * @return Result
     */
    public function authenticate(SubjectInterface $subject);
}
