<?php

use Phinx\Migration\AbstractMigration;

class DbMigration1456142964 extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('users', ['id' => false]);
        $table->addColumn('time_created', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('last_updated', 'integer', ['null' => false, 'default' => 0])
            ->update();
    }

    public function down()
    {
        $table = $this->table('users');
        $table->removeColumn('time_created')
            ->removeColumn('last_updated')
            ->update();
    }
}
