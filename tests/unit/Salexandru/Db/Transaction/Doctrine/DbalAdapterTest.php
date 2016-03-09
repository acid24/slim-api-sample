<?php

namespace Salexandru\Db\Transaction\Doctrine;

use Mockery as m;
use Doctrine\DBAL\Connection;

class DbalAdapterTest extends \PHPUnit_Framework_TestCase
{

    private $conn;

    protected function setUp()
    {
        $this->conn = m::mock(Connection::class);
    }

    public function testStartTransaction()
    {
        $this->conn
            ->shouldReceive('beginTransaction')
            ->once();

        $adapter = new DbalAdapter($this->conn);
        $adapter->startTransaction();
    }

    public function testCommitTransaction()
    {
        $this->conn
            ->shouldReceive('commit')
            ->once();

        $adapter = new DbalAdapter($this->conn);
        $adapter->commitTransaction();
    }

    public function testRollbackTransaction()
    {
        $this->conn
            ->shouldReceive('rollBack')
            ->once();

        $adapter = new DbalAdapter($this->conn);
        $adapter->rollbackTransaction();
    }

    public function testCheckTransactionIsActive()
    {
        $this->conn->shouldReceive('isTransactionActive')
            ->once()
            ->andReturn($isActive = (bool)mt_rand(0, 1));

        $adapter = new DbalAdapter($this->conn);
        $this->assertEquals($isActive, $adapter->isTransactionActive());
    }
}
