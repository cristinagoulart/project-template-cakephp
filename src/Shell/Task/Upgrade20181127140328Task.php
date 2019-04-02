<?php
namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\I18n\Time;

class Upgrade20181127140328Task extends Shell
{
    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');

        return $parser;
    }

    /**
     * main() method
     *
     * @return int|bool|null
     */
    public function main()
    {
        $task = $this->Tasks->load('ScheduledJobs');

        $task->add('CakeShell::App:scheduled_log', [
            'recurrence' => 'FREQ=DAILY;INTERVAL=1',
            'start_date' => new Time('02:00'),
            'options' => 'gc',
        ]);

        return true;
    }
}
