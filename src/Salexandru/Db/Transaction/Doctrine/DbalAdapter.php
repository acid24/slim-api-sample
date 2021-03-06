<?php

namespace Salexandru\Db\Transaction\Doctrine;

use Salexandru\Db\Transaction\AdapterInterface;
use Doctrine\DBAL\Connection;

class DbalAdapter implements AdapterInterface
{

    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Start a transaction
     *
     * @return void
     */
    public function startTransaction()
    {
        $this->conn->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return void
     */
    public function commitTransaction()
    {
        $this->conn->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        $this->conn->rollBack();
    }

    /**
     * Is a transaction active ATM?
     *
     * @return boolean
     */
    public function isTransactionActive()
    {
        return $this->conn->isTransactionActive();
    }
}
