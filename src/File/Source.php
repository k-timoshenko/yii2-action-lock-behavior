<?php

namespace tkanstantsin\Yii2ActionLockBehavior\File;

use tkanstantsin\Yii2ActionLockBehavior\ISource;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class Source
 */
class Source extends BaseObject implements ISource
{
    /**
     * @var string
     */
    public $basePidPath;

    private $pid;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->pid = (int) getmypid();

        if ($this->basePidPath === null || $this->basePidPath === '') {
            throw new InvalidConfigException('Path for PID cannot be empty');
        }
        if (!file_exists($this->basePidPath)) {
            if (!is_dir($this->basePidPath) && !mkdir($concurrentDirectory = $this->basePidPath) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory `%s` was not created', $concurrentDirectory));
            }
        }
    }


    /**
     * Check if current connection to source still works and locks pid
     *
     * @param string $fileName
     * @param string $uid
     *
     * @return bool
     */
    public function ensureActive(string $fileName, string $uid): bool
    {
        $filePath = $this->getPidPath($fileName);
        if (!file_exists($filePath)) {
            return false;
        }

        return $this->readPidFromFile($filePath) === $this->pid;
    }

    /**
     * Try lock pid in source
     *
     * @param string $fileName
     * @param string $uid
     *
     * @return bool
     */
    public function lock(string $fileName, string $uid): bool
    {
        $filePath = $this->getPidPath($fileName);

        $pid = $this->readPidFromFile($filePath);
        // Check if process yet exist in proc
        if ($pid !== null && file_exists('/proc/' . $pid)) {
            // Detail by process info
            exec('ps ' . $pid, $output, $result);

            if (\count($output) >= 2
                && preg_match('/' . addcslashes($fileName, '/') . '/i', $output[1] ?? null)
            ) {
                return false;
            }
        }

        $this->createPid($filePath);

        return true;
    }

    /**
     * Free (delete/rollback) pid from source
     *
     * @param string $fileName
     * @param string $uid
     *
     * @return bool
     */
    public function free(string $fileName, string $uid): bool
    {
        return $this->removePid($fileName);
    }


    /**
     * Get PID file path
     *
     * @param string $fileName
     * @return string
     */
    private function getPidPath(string $fileName): string
    {
        $fileName = preg_replace('/[^a-z0-9\-\.]/i', '_', $fileName);

        return $this->basePidPath . DIRECTORY_SEPARATOR . $fileName . '.pid';
    }

    /**
     * Create PID file
     *
     * @param string $filePath
     * @return bool
     */
    protected function createPid(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return false;
        }
        file_put_contents($filePath, $this->pid);
        @chmod($filePath, 0777);

        return true;
    }

    /**
     * Remove PID file
     *
     * @param string $fileName
     * @return bool
     */
    protected function removePid(string $fileName): bool
    {
        $filePath = $this->getPidPath($fileName);
        if (!file_exists($filePath)) {
            return true;
        }

        if ($this->readPidFromFile($filePath) === $this->pid) {
            @unlink($filePath);
        }

        return true;
    }

    protected function readPidFromFile(string $filePath): ?int
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $pid = file_get_contents($filePath);

        return (int) trim(preg_replace("/\r|\n/", '', $pid));
    }
}