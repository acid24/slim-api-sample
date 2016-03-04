<?php

namespace Salexandru\Api\Authentication\Doctrine;

use Doctrine\DBAL\Driver\Statement;
use Mockery as m;
use Salexandru\Api\Authentication\ApiClient;
use Salexandru\Authentication\SubjectInterface as Subject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalAuthenticationStrategyTest extends \PHPUnit_Framework_TestCase
{

    public function testWrongCredentialsTypeReturnsFailureResult()
    {
        $credentials = m::mock('Salexandru\\Authentication\\WrongTypeOfCredentials');
        $subject = m::mock(Subject::class)
            ->shouldReceive('getCredentials')
            ->once()
            ->andReturn($credentials)
            ->getMock();

        $connection = m::mock(Connection::class);

        $strategy = new DbalAuthenticationStrategy($connection);
        $result = $strategy->authenticate($subject);

        $this->assertTrue($result->isFailure());
    }

    public function testNonexistentUsernameReturnsFailureResult()
    {
        $subject = new ApiClient('test', 'test');
        $credentials = $subject->getCredentials();

        $stmt = m::mock(Statement::class);
        $stmt->shouldReceive('fetch')
            ->once()
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn(false);

        $qb = m::mock(QueryBuilder::class)->makePartial();
        $qb->shouldReceive('execute')
            ->once()
            ->andReturn($stmt);
        $qb->shouldReceive('setParameter')
            ->with('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->andReturnSelf();

        $connection = m::mock(Connection::class)
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb)
            ->getMock();

        $strategy = new DbalAuthenticationStrategy($connection);
        $result = $strategy->authenticate($subject);

        $this->assertTrue($result->isFailure());
    }

    public function testInvalidCredentialsReturnsFailureResult()
    {
        $subject = new ApiClient('test', 'test');
        $credentials = $subject->getCredentials();

        $stmt = m::mock(Statement::class);
        $stmt->shouldReceive('fetch')
            ->once()
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn(['password' => 'another-test']);

        $qb = m::mock(QueryBuilder::class)->makePartial();
        $qb->shouldReceive('execute')
            ->once()
            ->andReturn($stmt);
        $qb->shouldReceive('setParameter')
            ->with('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->andReturnSelf();

        $connection = m::mock(Connection::class)
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb)
            ->getMock();

        $strategy = new DbalAuthenticationStrategy($connection);
        $result = $strategy->authenticate($subject);

        $this->assertTrue($result->isFailure());
    }

    public function testValidCredentialsReturnsSuccessResult()
    {
        $subject = new ApiClient('test', 'test');
        $credentials = $subject->getCredentials();

        $stmt = m::mock(Statement::class);
        $stmt->shouldReceive('fetch')
            ->once()
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn(['password' => password_hash('test', PASSWORD_BCRYPT, ['cost' => 8])]);

        $qb = m::mock(QueryBuilder::class)->makePartial();
        $qb->shouldReceive('execute')
            ->once()
            ->andReturn($stmt);
        $qb->shouldReceive('setParameter')
            ->with('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->andReturnSelf();

        $connection = m::mock(Connection::class)
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb)
            ->getMock();

        $strategy = new DbalAuthenticationStrategy($connection);
        $result = $strategy->authenticate($subject);

        $this->assertTrue($result->isSuccess());
    }
}
