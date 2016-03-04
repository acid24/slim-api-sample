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

    public function testWrongCredentialsTypeReturnFailureResult()
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

    public function testInvalidCredentialsReturnFailureResult()
    {
        $subject = new ApiClient('test', 'test');
        $credentials = $subject->getCredentials();

        $stmt = m::mock(Statement::class);
        $stmt->shouldReceive('setFetchMode')
            ->once()
            ->with(\PDO::FETCH_NUM)
            ->getMock();
        $stmt->shouldReceive('fetchColumn')
            ->once()
            ->andReturn(0);

        $qb = m::mock(QueryBuilder::class)->makePartial();
        $qb->shouldReceive('execute')
            ->once()
            ->andReturn($stmt);
        $qb->shouldReceive('setParameter')
            ->with('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->with('password', $credentials->getPassword(), \PDO::PARAM_STR)
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

    public function testValidCredentialsReturnSuccessResult()
    {
        $subject = new ApiClient('test', 'test');
        $credentials = $subject->getCredentials();

        $stmt = m::mock(Statement::class);
        $stmt->shouldReceive('setFetchMode')
            ->once()
            ->with(\PDO::FETCH_NUM)
            ->getMock();
        $stmt->shouldReceive('fetchColumn')
            ->once()
            ->andReturn(1);

        $qb = m::mock(QueryBuilder::class)->makePartial();
        $qb->shouldReceive('execute')
            ->once()
            ->andReturn($stmt);
        $qb->shouldReceive('setParameter')
            ->with('username', $credentials->getUsername(), \PDO::PARAM_STR)
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->with('password', $credentials->getPassword(), \PDO::PARAM_STR)
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
