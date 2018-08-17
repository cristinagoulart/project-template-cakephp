<?php
namespace App\Shell\Task;

use App\Feature\Factory as FeatureFactory;
use App\ScheduledJobs\Handlers\CakeShellHandler;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class Upgrade20180511135300Task extends Shell
{
    /**
     * @var array $commandsToAdd for default deploy commands
     */
    public $commandsToAdd = [
        'CakeShell::App:database_log' => [
            // every 12 hours
            'recurrence' => 'FREQ=HOURLY;INTERVAL=12',
            'options' => 'cleanup',
        ],
        'CakeShell::CsvMigrations:import' => [
            // every 5 minutes
            'recurrence' => 'FREQ=MINUTELY;INTERVAL=5'
        ]
    ];

    /**
     * Manage the available sub-commands along with their arguments and help
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
     * @return null
     */
    public function main()
    {
        $feature = FeatureFactory::get('Module' . DS . 'ScheduledJobs');
        if (! $feature->isActive()) {
            $this->out('Scheduled Jobs are disabled. Skipping...');

            return false;
        }

        $cakeShellsHandler = new CakeShellHandler();

        $list = $cakeShellsHandler->getList();

        foreach ($list as $command) {
            if (in_array($command, array_keys($this->commandsToAdd))) {
                $this->addScheduleJob($command);
            }
        }
    }

    /**
     * Add Scheduled Job record if needed
     *
     * @param string $command to be added
     * @return bool $result whether the record was added.
     */
    protected function addScheduleJob($command = '')
    {
        $result = false;
        $scheduledJobsTable = TableRegistry::get('ScheduledJobs');

        if (empty($command)) {
            return $result;
        }

        $query = $scheduledJobsTable->find()
            ->where(['job' => $command]);
        $query->execute();

        if ($query->count()) {
            $entity = $query->first();
            $this->warn("Scheduled Job [$command] already added. Status [$entity->active]");

            return $result;
        }

        $entity = $scheduledJobsTable->newEntity();
        $entity->name = "System [$command] command";
        $entity->job = $command;
        $entity->recurrence = $this->commandsToAdd[$command]['recurrence'];
        $entity->active = true;
        $entity->start_date = Time::now();

        if (! empty($this->commandsToAdd[$command]['options'])) {
            $entity->options = $this->commandsToAdd[$command]['options'];
        }

        if ($scheduledJobsTable->save($entity)) {
            $this->success("Added Scheduled Job [$command] to the datatable.");
            $result = true;
        } else {
            $this->warn("Error adding scheduled job [$command] to database");
        }

        return $result;
    }
}
