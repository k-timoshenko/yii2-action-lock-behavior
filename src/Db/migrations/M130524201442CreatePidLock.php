<?php

namespace tkanstantsin\Yii2ActionLockBehavior\Db\migrations;

use yii\db\Migration;

/**
 * Class M130524201442CreatePidLock
 * @package tkanstantsin\Yii2ActionLockBehavior\Db\migrations
 * @author Konstantin Timoshenko
 * @author Yarmaliuk Mikhail
 * @version 2.0
 *
 * @since 2.0 add namespace, phpdoc
 * @since 1.0 not namespace
 */
class M130524201442CreatePidLock extends Migration
{
    /**
     * @var string
     */
    public $tLock = '{{%pid_lock}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tLock, [
            'id' => $this->primaryKey(),
            'pid' => $this->string(255)->notNull()->unique()->comment('process'),
        ]);

        $this->createIndex('pid_lock_pid_uidx', $this->tLock, 'pid', true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tLock);
    }
}