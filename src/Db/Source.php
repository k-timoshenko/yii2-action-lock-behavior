<?php

namespace tkanstantsin\Yii2ActionLockBehavior\Db;

use tkanstantsin\Yii2ActionLockBehavior\ISource;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Transaction;
use yii\di\Instance;

/**
 * Class    Source
 * @package tkanstantsin\Yii2ActionLockBehavior\Db
 * @version 1.0
 */
class Source extends BaseObject implements ISource
{
    /**
     * @var Connection|string
     */
    public $connection;

    /**
     * @var string
     */
    public $tableName = '{{%pid_lock}}';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->connection = Instance::ensure($this->connection, Connection::class);
    }

    /**
     * @inheritdoc
     */
    public function ensureActive(string $pid, ?string $id): bool
    {
        if ($this->connection->transaction === null || !$this->connection->transaction->isActive) {
            return false;
        }

        $lock = (new Query())
            ->from($this->tableName)
            ->andFilterWhere(['id' => $id])
            ->andWhere(['pid' => $pid])
            ->exists($this->connection);

        return $lock;
    }

    /**
     * @inheritdoc
     */
    public function lock(string $pid, string $id): bool
    {
        $this->connection->beginTransaction(Transaction::READ_UNCOMMITTED);

        // check if another process lock this pid
        if ($this->ensureActive($pid, null)) { // better performance than just trying to save
            return false;
        }

        try {
            return (bool) $this->connection->createCommand()
                ->insert($this->tableName, [
                    'id'  => $id,
                    'pid' => $pid,
                ])
                ->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function free(string $pid, string $id): bool
    {
        if ($this->connection->transaction !== null
            && $this->connection->transaction->isActive
        ) {
            $this->connection->transaction->rollBack();
        }

        return true;
    }
}