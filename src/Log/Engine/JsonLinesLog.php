<?php

namespace App\Log\Engine;

use Cake\Log\Engine\FileLog;
use DateTime;

class JsonLinesLog extends FileLog
{
    use LevelScopeAwareTrait;

    /**
     * Implements writing to log files as JSON Lines
     * @link http://jsonlines.org/
     *
     * @param string $level The severity level of the message being written.
     *    See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @param array $context Additional information about the logged message
     * @return bool success of write.
     */
    public function log($level, $message, array $context = []): bool
    {
        if (! $this->matchesLevelAndScope($level, $context)) {
            return false;
        }

        $entry = [];
        $entry['datetime'] = date(DateTime::ISO8601);
        $entry['level'] = ucfirst($level);
        $entry['message'] = $message;
        $entry['context'] = $context;

        $output = (string)json_encode($entry) . "\n";

        $filename = $this->_getFilename($level);
        if ($this->_size) {
            $this->_rotateFile($filename);
        }

        $pathname = $this->_path . $filename;
        $mask = $this->_config['mask'];
        if (!$mask) {
            return (bool)file_put_contents($pathname, $output, FILE_APPEND);
        }

        $exists = file_exists($pathname);
        $result = (bool)file_put_contents($pathname, $output, FILE_APPEND);
        static $selfError = false;

        if (!$selfError && !$exists && !chmod($pathname, (int)$mask)) {
            $selfError = true;
            trigger_error(vsprintf(
                'Could not apply permission mask "%s" on log file "%s"',
                [$mask, $pathname]
            ), E_USER_WARNING);
            $selfError = false;
        }

        return $result;
    }
}
