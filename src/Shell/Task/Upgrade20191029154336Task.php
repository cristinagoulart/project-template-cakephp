<?php

namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\I18n\Time;

class Upgrade20191029154336Task extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Adding scheduled job for running exports garbage collection once a day.');

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

        $task->add('CakeShell::App:export', [
            'recurrence' => 'FREQ=DAILY;INTERVAL=1',
            'start_date' => new Time('now'),
            'options' => 'gc',
        ]);

        return true;
    }
}
