<?php

use yii\db\Migration;

class m180708_141141_create_pidLock extends Migration
{
    public $tLock = '{{%pid_lock}}';

    public function safeUp()
    {
        $this->createTable($this->tLock, [
            'id'  => $this->primaryKey(),
            'pid' => $this->string(255)->notNull()->unique()->comment('process'),
        ]);

        $this->createIndex('pid_lock_route_uidx', $this->tLock, 'pid', true);
    }

    public function safeDown()
    {
        $this->dropTable($this->tLock);
    }
}