<?php

namespace Salexandru\Db\Logging\Doctrine;

use Doctrine\DBAL\Connection;
use Mockery as m;
use Psr\Log\LoggerInterface as PsrLogger;

class DbalSqlLoggerTest extends \PHPUnit_Framework_TestCase
{

    private $psrLogger;

    protected function setUp()
    {
        $this->psrLogger = m::mock(PsrLogger::class);
    }

    public function testLoggingSqlWithNoParameters()
    {
        $sql = 'SELECT * FROM T';

        $expectedMessage = 'Received query {sql}';
        $expectedContext = ['sql' => $sql];

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $logger = new DbalSqlLogger($this->psrLogger);
        $logger->startQuery($sql);
    }

    public function testLoggingSqlWithPositionalParameters()
    {
        $sql = 'SELECT * FROM T WHERE C1 = ? AND C2 = ? AND C3 != ? AND C4 > ?';
        $params = [1, 'test', false, 0.01];

        $transformedSql = "SELECT * FROM T WHERE C1 = 1 AND C2 = 'test' AND C3 != false AND C4 > 0.01";

        $expectedMessage = 'Received query {sql}';
        $expectedContext = ['sql' => $transformedSql];

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $logger = new DbalSqlLogger($this->psrLogger);
        $logger->startQuery($sql, $params);
    }

    public function testLoggingSqlWithInClause()
    {
        $sql = 'SELECT * FROM T WHERE C1 = ? AND C2 IN (?)';
        $params = [true, ['test', 'pest', 'best']];
        $types = [\PDO::PARAM_BOOL, Connection::PARAM_STR_ARRAY];

        $transformedSql = "SELECT * FROM T WHERE C1 = true AND C2 IN ('test', 'pest', 'best')";

        $expectedMessage = 'Received query {sql}';
        $expectedContext = ['sql' => $transformedSql];

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $logger = new DbalSqlLogger($this->psrLogger);
        $logger->startQuery($sql, $params, $types);
    }

    public function testLoggingSqlWithNamedParameters()
    {
        $sql = 'SELECT * FROM T WHERE C1 = :c1 AND C2 = :c2 AND C3 != :c3 AND C4 > :c4';
        $params = ['c1' => 1, 'c2' => 'test', 'c3' => false, 'c4' => 0.01];

        $transformedSql = "SELECT * FROM T WHERE C1 = 1 AND C2 = 'test' AND C3 != false AND C4 > 0.01";

        $expectedMessage = 'Received query {sql}';
        $expectedContext = ['sql' => $transformedSql];

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $logger = new DbalSqlLogger($this->psrLogger);
        $logger->startQuery($sql, $params);
    }

    public function testQueryExecutionTimeIsLogged()
    {
        $sql = 'SELECT * FROM T';

        $expectedMessage = 'Received query {sql}';
        $expectedContext = ['sql' => $sql];

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext);

        $expectedMessage = 'Query took {time} seconds';
        $expectedContext = \Mockery::on(function (array $context) {
            if (!isset($context['time'])) {
                return false;
            }

            return is_float($context['time']) && $context['time'] > 0;
        });

        $this->psrLogger->shouldReceive('debug')
            ->with($expectedMessage, $expectedContext);

        $logger = new DbalSqlLogger($this->psrLogger);
        $logger->startQuery($sql);
        $logger->stopQuery();
    }
}
