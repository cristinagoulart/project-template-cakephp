<?php
namespace App\Shell\Task;

use App\Feature\Factory as FeatureFactory;
use App\ScheduledJobs\Handlers\CakeShellHandler;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class Upgrade201808061323Task extends Shell
{
    /**
     * @var array $commandsToAdd for default deploy commands
     */
    public $commandsToAdd = [
        'CakeShell::App:avatars_sync' => [
            'recurrence' => 'FREQ=HOURLY;INTERVAL=1',
        ],
    ];

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
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
     * @retun void
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
