<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Utility\Inflector;
use Exception;
use Qobo\Utils\Utility\Lock\FileLock;

class LockTask extends Shell
{
    /**
     * Generate lock file. Abort if lock file is already generated.
     *
     * @param string $file Path to the shell script which acquires lock
     * @param string $class Name of the shell class which acquires lock
     * @return \Qobo\Utils\Utility\FileLock
     */
    public function lock(string $file, string $class)
    {
        $lockFile = $this->getLockFileName($file, $class);

        try {
            $lock = new FileLock($lockFile);
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            $this->abort('Task is already in progress');
        }

        return $lock;
    }

    /**
     * getLockFileName method
     *
     * @param string $file Path to the shell script which acquires lock
     * @param string $class Name of the shell class which acquires lock
     * @return string Unique lock file name
     */
    public function getLockFileName(string $file, string $class): string
    {
        $class = Inflector::underscore(preg_replace('/\\\/', '', $class));
        $lockFile = $class . '_' . md5($file) . '.lock';

        return $lockFile;
    }
}
