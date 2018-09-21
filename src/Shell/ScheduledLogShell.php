<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * ScheduledLogShell shell command.
 *
 * Delete
 *
 */
class ScheduledLogShell extends Shell
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

        $parser->description('Sheduled job logs.');
        $parser->addSubcommand('gc', [
            'help' => 'Clean scheduled jog logs',
            'parser' => [
                'options' => [
                    'age' => [
                        'help' => __('Time to delete (ex: -1 day / -1 months)'),
                        'required' => false,
                        'short' => 'a'
                    ]
                ]
            ],
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function gc()
    {
        $age = $this->getDaysConfig();
        // Delete before this day
        $date = new Time($age);
        $query = TableRegistry::get('ScheduledJobLogs');
        // Count how many has been deleted
        $count = $query->deleteAll(['created <' => $date]);
        $this->info('Removed ' . number_format($count) . ' log records older than ' . ltrim($age, '-') . '.');
        // Write in the Log
        Log::write('info', "Clean up scheduled job logs older then $age.");

        return null;
    }

    /**
     * Get stats log configuration
     *
     * @return string
     */
    protected function getDaysConfig()
    {
        if (isset($this->params['age'])) {
            $result = $this->params['age'];

            return $result;
        }

        $result = Configure::read('ScheduledLog.stats.age');
        if (!$result) {
            $this->info('Required parameter "age" is not defined (garbage collector)');

            return null;
        }

        return $result;
    }
}
