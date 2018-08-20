<?php

namespace tkanstantsin\Yii2ActionLockBehavior;

use yii\base\Behavior;
use yii\base\Controller;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\log\Logger;

/**
 * Class ActionLockBehavior
 * @package tkanstantsin\Yii2ActionLockBehavior
 * @version 1.0
 */
class ActionLockBehavior extends Behavior
{
    public const PID_LENGTH_LIMIT = 255;

    /**
     * @var ISource
     */
    public $source;

    /**
     * Whether to send messages into terminal or not
     *
     * @var bool
     */
    public $allowStdOut = true;

    /**
     * @var Logger|null
     */
    public $logger;

    /**
     * @var string
     */
    public $loggerCategory = self::class;

    /**
     * @var int
     */
    public $pidLengthLimit = self::PID_LENGTH_LIMIT;

    /**
     * Request route by default or \Closure to generate user-specific PID.
     *
     * @var string|\Closure|null
     */
    protected $pid;

    /**
     * @var string
     */
    protected $uid;

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->free();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if ($this->logger !== null) {
            $this->logger = Instance::ensure($this->logger, Logger::class);
        }

        $this->source = Instance::ensure($this->source, ISource::class);
    }

    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return ArrayHelper::merge(parent::events(), [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
            Controller::EVENT_AFTER_ACTION => 'afterAction',
        ]);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function beforeAction($event): bool
    {
        if ($this->pid instanceof \Closure) {
            $this->pid = \call_user_func($this->pid, $event);
        } else {
            // try get route if behavior attached to action/controller
            $this->pid = $event->action->controller->module->requestedRoute ?? null;
        }

        if (mb_strlen($this->pid) > $this->source->getPidMaxLength()) {
            $this->log(sprintf('PID length must be smaller than %s symbols', $this->pidLengthLimit), Logger::LEVEL_INFO);

            return $event->isValid = false;
        }

        $this->uid = random_int(1, 1000 * 1000);

        if (!$this->lock()) {
            $this->log('Cannot lock pid record', Logger::LEVEL_INFO);

            return $event->isValid = false;
        }

        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($event): bool
    {
        $this->free();

        return $event->isValid;
    }

    /**
     * Check process pid (for linux system)
     * @see ISource::lock()
     *
     * @return bool
     */
    public function lock(): bool
    {
        if ($this->pid === null || $this->pid === ''
            || $this->uid === null || $this->uid === ''
        ) {
            $this->log('PID and UID cannot be empty', Logger::LEVEL_INFO);

            return false;
        }

        $locked = $this->source->lock($this->pid, $this->uid);

        if (!$locked) {
            $this->log("PID `{$this->pid}` already locked", Logger::LEVEL_INFO);
        }

        return $locked;
    }

    /**
     * Touch lock if it exist or db is gone away
     * @see ISource::ensureActive()
     */
    public function ensureActive(): bool
    {
        $locked = $this->source->ensureActive($this->pid, $this->uid);

        if (!$locked) {
            $this->log("PID lock for `{$this->pid}` expired", Logger::LEVEL_INFO);
        }

        return $locked;
    }

    /**
     * @see ISource::free()
     * @return bool
     */
    public function free(): bool
    {
        $freed = $this->source->free($this->pid, $this->uid);

        if (!$freed) {
            $this->log("Cannot free PID `{$this->pid}` from source", Logger::LEVEL_INFO);
        }

        return $freed;
    }

    /**
     * @param string $message
     */
    protected function output(string $message): void
    {
        if (!$this->allowStdOut) {
            return;
        }

        Console::output($message);
    }

    /**
     * @param string $message
     * @param int $level
     */
    protected function log(string $message, int $level): void
    {
        $this->output($message);

        if ($this->logger === null) {
            return;
        }

        $this->logger->log($message, $level, $this->loggerCategory);
    }
}