<?php

namespace tkanstantsin\Yii2ActionLockBehavior\Db;

use tkanstantsin\Yii2ActionLockBehavior\ISource;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Transaction;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Source
 * @package tkanstantsin\Yii2ActionLockBehavior\Db
 * @author  Konstantin Timoshenko
 * @author  Yarmaliuk Mikhail
 * @version 1.1
 *
 * @since   1.1 add copy exist connection
 * @since   1.0 need new connection config
 */
class Source extends BaseObject implements ISource
{
    /**
     * @var Connection|string
     */
    public $connection;

    /**
     * @var bool
     */
    public $connectionCopy = true;

    /**
     * @var array
     */
    public $connectionAttributes = [
        // Permanent mysql connection
        \PDO::ATTR_PERSISTENT => true,
    ];

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

        if ($this->connectionCopy) {
            $this->connection = Instance::of($this->connection)->get();
            // Merger connection attributes
            $this->connection->attributes = ArrayHelper::merge(
                (array) $this->connection->attributes,
                (array) $this->connectionAttributes
            );
        }

        $this->connection = Instance::ensure($this->connection, Connection::class);
    }

    /**
     * @inheritdoc
     */
    public function ensureActive(string $pid, ?string $id): bool
    {
        if ($this->connection->transaction === NULL
            || !$this->connection->transaction->isActive
        ) {
            return false;
        }

        return (bool) (new Query())
            ->from($this->tableName)
            ->andFilterWhere(['id' => $id])
            ->andWhere(['pid' => $pid])
            ->exists($this->connection);
    }

    /**
     * @inheritdoc
     */
    public function lock(string $pid, string $id): bool
    {
        $this->connection->beginTransaction(Transaction::READ_UNCOMMITTED);

        // check if another process lock this pid
        if ($this->ensureActive($pid, NULL)) { // better performance than just trying to save
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
        if ($this->connection->transaction !== NULL
            && $this->connection->transaction->isActive
        ) {
            $this->connection->transaction->rollBack();
        }

        return true;
    }
}