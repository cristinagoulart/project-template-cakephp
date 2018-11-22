<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Utility\Inflector;
use Exception;
use Qobo\Utils\Utility\Lock\FileLock;
use Qobo\Utils\Utility\Lock\LockInterface;
use RuntimeException;

class LockTask extends Shell
{
    /**
     * Generate lock file. Abort if lock file is already generated.
     *
     * @param string $file Path to the shell script which acquires lock
     * @param string $class Name of the shell class which acquires lock
     * @return \Qobo\Utils\Utility\Lock\LockInterface
     */
    public function lock(string $file, string $class): LockInterface
    {
        $lockFile = $this->getLockFileName($file, $class);

        try {
            $lock = new FileLock($lockFile);
        } catch (Exception $e) {
            throw new RuntimeException("Couldn't create a lock file for $file", 0, $e);
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
        /**
         * @var string $className
         */
        $className = preg_replace('/\\\/', '', $class);

        $class = Inflector::underscore($className);

        $lockFile = $class . '_' . md5($file) . '.lock';

        return $lockFile;
    }
}
