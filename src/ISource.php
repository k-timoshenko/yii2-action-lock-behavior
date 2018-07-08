<?php

namespace tkanstantsin\Yii2ActionLockBehavior;

interface ISource
{
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
}