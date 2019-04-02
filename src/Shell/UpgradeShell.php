<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Filesystem\Folder;

class UpgradeShell extends Shell
{
    /**
     * Tasks are set automagically during initialization (see initialize method below).
     *
     * @var array
     */
    public $tasks = [];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $this->tasks = $this->fetchTasks();

        parent::initialize();
    }

    /**
     * Get option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Upgrade Shell');

        foreach ($this->tasks as $task) {
            $parser->addSubcommand(strtolower($task), [
                'help' => $this->{$task}->getOptionParser()->getDescription(),
                'parser' => $this->{$task}->getOptionParser(),
            ]);
        }

        return $parser;
    }

    /**
     * Shell entry point
     *
     * @return int|bool|null
     */
    public function main()
    {
        foreach ($this->tasks as $task) {
            $this->info(sprintf('Running task %s', $task));
            $this->{$task}->main();
        }
    }

    /**
     * Fetches upgrade related tasks from src/Shell/Task directory
     *
     * @return mixed[]
     */
    private function fetchTasks(): array
    {
        $dir = new Folder(__DIR__ . DS . 'Task');

        $result = [];
        foreach ($dir->find('Upgrade(\d+)Task\.php') as $file) {
            $result[] = str_replace('Task.php', '', $file);
        }

        sort($result);

        return $result;
    }
}
