<?php

namespace App\Log\Engine;

use Cake\Database\Log\LoggedQuery;
use DatabaseLog\Log\Engine\DatabaseLog;

class LevelAwareDatabaseLog extends DatabaseLog
{
    use LevelScopeAwareTrait;

    /**
     * Write the log to database. Skip writing logs if log level is not supported.
     *
     * @param string $level Log level
     * @param mixed $message Log message
     * @param array $context Log context
     * @return bool Success
     */
    public function log($level, $message, array $context = []): bool
    {
        // avoid logging database queries, which results in infinite recursion
        if ($message instanceof LoggedQuery) {
            return false;
        }

        if (! $this->matchesLevelAndScope($level, $context)) {
            return false;
        }

        return parent::log($level, $message, $context);
    }
}
