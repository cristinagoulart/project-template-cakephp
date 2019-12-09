<?php

namespace App\Shell;

use Cake\Shell\PluginShell as CorePluginShell;

/**
 * Custom Plugin Shell class that adds extended
 * functionality to Cake's core Plugin Shell.
 *
 * @property \App\Shell\Task\ListTask $List
 * @property \App\Shell\Task\MigrationsTask $Migrations
 *
 */
class PluginShell extends CorePluginShell
{
    /**
     * Tasks to load
     *
     * @var array
     */
    public $tasks = [
        'List',
        'Migrations',
    ];

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->setDescription('Qobo Plugin Shell adds extended functionality to Cake\'s Plugin Shell.')
            ->addSubcommand('list', ['help' => 'List all loaded plugins', 'parser' => $this->List->getOptionParser()])
            ->addSubcommand(
                'migrations',
                ['help' => 'Migration tasks for all loaded plugins', 'parser' => $this->Migrations->getOptionParser()]
            );

        return $parser;
    }

    /**
     *  No welcome message in the cake shell output
     *
     * @return void
     */
    protected function _welcome()
    {
    }
}
