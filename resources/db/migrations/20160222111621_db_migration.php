<?php

use Phinx\Migration\AbstractMigration;

class DbMigration extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('users', ['id' => false]);
        $table->addColumn('id', 'integer', ['identity' => true, 'null' => false, 'default' => 0])
            ->addColumn('username', 'string', ['limit' => 255, 'null' => false, 'default' => ''])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false, 'default' => ''])
            ->addIndex('username', ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->dropTable('users');
    }
}
