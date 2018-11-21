<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * SoftDelete shell command.
 *
 */
class SoftDeleteShell extends Shell
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

        $parser->setDescription('Permanently delete soft-deleted record');
        $parser->addArguments([
            'module' => [
                'help' => 'The module to clean from trashed records',
                'required' => false,
                'choices' => $this->validArgument()
            ],
            'force' => [
                'help' => 'Force the permanent delete',
                'required' => false,
                'choices' => ['force']
            ]
        ]);
        $parser->addOptions([
            'age' => [
                'help' => __('Time to delete (ex: -1 day / -1 months)'),
                'required' => false,
                'short' => 'a'
            ]
        ]);

        return $parser;
    }

    /**
     * Main method.
     * @return void|string
     */
    public function main()
    {
        $module = isset($this->args[0]) ? $this->args[0] : null;
        // if no arguments will print the list of modules
        if (!$module) {
            $modules = $this->listModules();
            foreach ($modules as $value) {
                $this->out($value);
            }

            return;
        }

        if ($module === 'all') {
            $this->cleanAll();

            return;
        }

        $this->cleanModule($module);
    }

    /**
     * Clean from trashed records all the Modules.
     *
     * @return null Success or error code.
     */
    private function cleanAll()
    {
        $folders = $this->listModules();

        foreach ($folders as $module) {
            $this->cleanModule($module);
        }

        return null;
    }

    /**
     * If the argument 'force' is not set, it will display the trash info about the selected modules
     * @param string $module Module to analyse/clean
     * @return void
     */
    private function cleanModule($module)
    {
        $force = isset($this->args[1]);

        $query = TableRegistry::get($module);

        if (!$query->behaviors()->has('Trash')) {
            // Module don't support trash
            return;
        }

        try {
            $trashEntities = TableRegistry::get($module)->find('onlyTrashed')->toArray();
        } catch (\BadMethodCallException $e) {
            // The module has not trash entities
            return;
        }

        if (!$force) {
            $this->out("The module $module had " . count($trashEntities) . " record(s) in the trash.");

            return;
        }

        $age = $this->getDaysConfig();
        $date = new Time($age);
        $count = $query->removeBehavior('Trash')->deleteAll(['trashed <' => $date]);
        Log::write('info', "Clean up $module: " . number_format($count) . " trash record older than $age.");
    }

    /**
     * Get a list of all the CSV Modules
     * @return array
     */
    private function listModules()
    {
        $dir = new Folder(Configure::read('CsvMigrations.modules.path'));
        $folders = $dir->read(true)[0];

        return (array)$folders;
    }

    /**
     * Validate script arguments
     * @return array list of CSV modules + 'all'
     */
    private function validArgument()
    {
        $args = $this->listModules();
        $args[] = 'all';

        return (array)$args;
    }

    /**
     * Get age from configuration or parameter
     *
     * @return string
     * @throws \RuntimeException
     */
    private function getDaysConfig(): string
    {
        if (isset($this->params['age'])) {
            return $this->params['age'];
        }

        $result = Configure::read('SoftDelete.stats.age');
        if ($result) {
            return $result;
        }

        $this->info('Parameter "age" is not set in config/SoftDelete');

        throw new RuntimeException('Parameter "age" is not set in config/SoftDelete');
    }
}
