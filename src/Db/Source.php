<?php

namespace tkanstantsin\Yii2ActionLockBehavior\Db;

use tkanstantsin\Yii2ActionLockBehavior\ISource;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Instance;
use malkusch\lock\mutex\MySQLMutex;

/**
 * Class Source
 * @package tkanstantsin\Yii2ActionLockBehavior\Db
 * @version 1.0
 */
class Source extends BaseObject implements ISource
{
    private const PID_MAX_LENGTH = 64;

    /**
     * @var Connection|string
     */
    public $connection;

    /**
     * Time to wait for lock
     * @var int
     */
    public $timeout = 0;

    /**
     * @var MySQLMutex
     */
    private $mutex;

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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function lock(string $pid, string $id): bool
    {
        try {
            $this->mutex = $this->createMutex($pid, $this->timeout);
            $this->mutex->lock();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @throws \malkusch\lock\exception\LockReleaseException
     */
    public function free(string $pid, string $id): bool
    {
        if ($this->mutex !== null) {
            $this->mutex->unlock();
        }

        return true;
    }

    /**
     * @param string $pid
     *
     * @return MysqlMutex
     * @throws \yii\db\Exception
     */
    protected function createMutex(string $pid, int $timeout): MysqlMutex
    {
        $this->connection->open();

        return new MySQLMutex($this->connection->pdo, $pid, $timeout);
    }

    /**
     * @inheritdoc
     */
    public function getPidMaxLength(): int
    {
        return self::PID_MAX_LENGTH;
    }
}