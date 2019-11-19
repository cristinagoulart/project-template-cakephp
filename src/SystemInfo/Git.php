<?php

namespace App\SystemInfo;

use RuntimeException;

/**
 * Git class
 *
 * This is a helper class that assists with
 * fetching a variety of git information
 * from the system.
 */
class Git
{
    /**
     * @var array $commands Git command shortcodes
     */
    protected static $commands = [
        'localChanges' => 'git status --porcelain',
        'currentHash' => 'git rev-parse --short HEAD',
    ];

    /**
     * Get command line and arguments for a given command
     *
     * @throws \RuntimeException when the command is not defined
     * @param string $command Git command to expand
     * @return string
     */
    public static function getCommand(string $command): string
    {
        if (empty(static::$commands[$command])) {
            throw new RuntimeException("Git command [$command] is not defined");
        }

        return static::$commands[$command];
    }

    /**
     * Get local changes
     *
     * @return string[]
     */
    public static function getLocalChanges(): array
    {
        $result = [];

        $command = static::getCommand('localChanges');

        $changes = shell_exec($command);
        $changes = $changes ? trim($changes) : '';
        if (empty($changes)) {
            return $result;
        }

        $result = explode("\n", $changes);

        return $result;
    }

    /**
     * Get the last commit short hash
     *
     * @return string
     */
    public static function getCurrentHash(): string
    {
        $command = static::getCommand('currentHash');

        return (string)shell_exec($command);
    }
}
