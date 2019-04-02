<?php
namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

class Upgrade20180806132300Task extends Shell
{
    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Adding avatars sync scheduled job');

        return $parser;
    }

    /**
     * main() method
     *
     * @return int|bool|null
     */
    public function main()
    {
        $this->Tasks->load('ScheduledJobs')
            ->add('CakeShell::App:avatars_sync', [
                'recurrence' => 'FREQ=HOURLY;INTERVAL=1',
            ]);
    }
}
