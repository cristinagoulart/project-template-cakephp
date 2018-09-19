<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * ScheduleLogShell shell command.
 *
 * Delete
 *
 */
class SchedulerLogShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->description('Clean Sheduled job logs.');
        /*
        To add...

        $parser->addOption('number', [
            'short' => 'n',
            'help' => 'Number records of logs to keep.',
            'default' => 10
        ]);
        */

        $parser->addOption('days', [
            'short' => 'd',
            'help' => 'Number of days of record to keep.',
            'default' => 30
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $days = $this->param('days');
        // Delete before this day
        $date = new Time($days . ' days ago');
        $query = TableRegistry::get('ScheduledJobLogs');
        $count = $query->deleteAll(['created <' => $date]);
        $this->info('Removed ' . number_format($count) . ' log records older than ' . ltrim($days, '-') . ' days.');
        // Write in the Log ?
        Log::warning("Clean up scheduled job logs older then $days days.");

        return null;
    }
}
