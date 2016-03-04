<?php

namespace Salexandru\Api\Authentication\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Salexandru\Authentication\AuthenticationStrategyInterface as AuthenticationStrategy;
use Salexandru\Authentication\Result;
use Salexandru\Authentication\SubjectInterface as Subject;
use Salexandru\Authentication\UsernameAndPasswordCredentials;

class DbalAuthenticationStrategy implements AuthenticationStrategy
{

    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param Subject $subject
     * @return Result
     */
    public function authenticate(Subject $subject)
    {
        $credentials = $subject->getCredentials();
        if (!$credentials instanceof UsernameAndPasswordCredentials) {
            $error = 'Doctrine DBAL authentication works only with username and password credentials';
            return Result::genericFailure($error);
        }

        /** @var Statement $stmt */
        $stmt = $this->conn->createQueryBuilder()
            ->select('password')
            ->from('users')
            ->where('username = :username')
            ->setMaxResults(1)
            ->setParameter('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (false === $row) {
            return Result::invalidCredentialsFailure('Invalid username and/or password');
        }

        if (!password_verify($credentials->getPassword(), $row['password'])) {
            return Result::invalidCredentialsFailure('Invalid username and/or password');
        }

        return Result::success();
    }
}
