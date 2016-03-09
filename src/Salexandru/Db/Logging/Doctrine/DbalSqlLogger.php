<?php

namespace Salexandru\Db\Logging\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\SQLParserUtils;
use Psr\Log\LoggerInterface as PsrLogger;

class DbalSqlLogger implements SQLLogger
{

    /**
     * @var PsrLogger
     */
    private $psrLogger;

    /**
     * @var float
     */
    private $timeStarted;

    public function __construct(PsrLogger $psrLogger)
    {
        $this->psrLogger = $psrLogger;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (null !== $params) {
            $sql = $this->replaceSqlParamsWithValues($sql, $params, $types ?: []);
        }
        $this->psrLogger->debug('Received query {sql}', ['sql' => $sql]);
        $this->timeStarted = microtime(true);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $timeEnded = microtime(true);
        $time = $timeEnded - $this->timeStarted;

        $this->psrLogger->debug('Query took {time} seconds', ['time' => $time]);
    }

    private function replaceSqlParamsWithValues($sql, array $params, array $types)
    {
        $hasPositionalParams = is_int(key($params));
        if ($hasPositionalParams) {
            list($sql, $params) = SQLParserUtils::expandListParameters($sql, $params, $types);
        }

        $transformedSql = $sql;
        $placeholderPositions = SQLParserUtils::getPlaceholderPositions($sql, $hasPositionalParams);

        $deviationAmount = 0;
        $offset = 0;
        foreach ($placeholderPositions as $key => $val) {
            if ($hasPositionalParams) {
                $paramVal = $params[$offset];
            } else {
                $paramVal = isset($params[$val]) ? $params[$val] : $params[":$val"];
            }

            $paramVal = var_export($paramVal, true);

            $position = ($hasPositionalParams ? $val : $key) + $deviationAmount;
            $length = $hasPositionalParams ? 1 : strlen($val) + 1;

            $transformedSql = substr_replace($transformedSql, $paramVal, $position, $length);
            $offset++;
            $deviationAmount += strlen($paramVal) - $length;
        }

        return $transformedSql;
    }
}
