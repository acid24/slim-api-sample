<?php

use Phinx\Migration\AbstractMigration;

class DbMigration1457121707 extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('password', 'string', ['limit' => 60, 'null' => false, 'default' => ''])
            ->save();
    }

    public function down()
    {
        $table = $this->table('users');
        $table->changeColumn('password', 'string', ['limit' => 255, 'null' => false, 'default' => ''])
            ->save();
    }
}
