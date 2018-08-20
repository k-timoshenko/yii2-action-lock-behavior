<?php

namespace tkanstantsin\Yii2ActionLockBehavior;

/**
 * Interface ISource
 * @package tkanstantsin\Yii2ActionLockBehavior
 */
interface ISource
{
    /**
     * Default sugested pid length limit. Some sources (MySQL) may restrict with own limits
     */
    public const DEFAULT_PID_MAX_LENGTH = 255;

    /**
     * Check if current connection to source still works and locks pid
     *
     * @param string $pid
     * @param string $uid
     *
     * @return bool
     */
    public function ensureActive(string $pid, string $uid): bool;

    /**
     * Try lock pid in source
     *
     * @param string $pid
     * @param string $uid
     *
     * @return bool
     */
    public function lock(string $pid, string $uid): bool;

    /**
     * Free (delete/rollback) pid from source
     *
     * @param string $pid
     * @param string $uid
     *
     * @return bool
     */
    public function free(string $pid, string $uid): bool;

    /**
     * Pid length may differs thourgh sources.
     * @return int
     */
    public function getPidMaxLength(): int;
}