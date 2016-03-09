<?php

namespace Salexandru\Db\Transaction;

interface AdapterInterface
{

    /**
     * Start a transaction
     *
     * @return void
     */
    public function startTransaction();

    /**
     * Commit a transaction
     *
     * @return void
     */
    public function commitTransaction();

    /**
     * Rollback a transaction
     *
     * @return void
     */
    public function rollbackTransaction();
}
