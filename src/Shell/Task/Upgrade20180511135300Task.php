<?php
namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

class Upgrade20180511135300Task extends Shell
{
    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Adding default scheduled jobs to db, if not added before.');

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

        $task->add('CakeShell::App:database_log', [
            // every 12 hours
            'recurrence' => 'FREQ=HOURLY;INTERVAL=12',
            'options' => 'cleanup',
        ]);

        $task->add('CakeShell::CsvMigrations:import', [
            // every 5 minutes
            'recurrence' => 'FREQ=MINUTELY;INTERVAL=5'
        ]);

        return true;
    }
}
