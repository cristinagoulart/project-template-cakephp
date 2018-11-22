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
 * Trash shell command.
 *
 */
class TrashShell extends Shell
{
    /**
     * Get the option parser for this shell.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $option = [
            'age' => [
                'help' => __('Time to delete (ex: -1 day / -1 months)'),
                'required' => false,
                'short' => 'a'
            ],
            'dry-run' => [
                'help' => __('Permanently delete soft-deleted record)'),
                'required' => false,
                'boolean' => true
            ]
        ];
        $parser->addSubcommand('list_modules', [
            'help' => 'Show a list of all available CSV modules.',
            'required' => false
        ]);
        $parser->addSubcommand('clear_all', [
            'help' => 'Clear all trashed records.',
            'parser' => [
                'description' => [
                    'Clear the trashed records for the supperted modules.',
                    'Use `cake cache list_modules` to list available CSV modules'
                ],
                'options' => $option
            ]
        ]);
        $parser->addSubcommand('clear', [
            'help' => 'Clear the trashed records for a specified module.',
            'parser' => [
                'description' => [
                    'Clear the trashed records for a specified module.',
                    'For example, `cake trash clear _cake_module_` will clear the module trashed recors',
                    'Use `cake cache list_modules` to list available CSV modules'
                ],
                'arguments' => [
                    'prefix' => [
                        'help' => 'The cache prefix to be cleared.',
                        'required' => false
                    ]
                ],
                'options' => $option
            ]
        ]);

        return $parser;
    }

    /**
     * List of all CSV Modules
     * @return void
     */
    public function listModules()
    {
        $modules = $this->getList();
        foreach ($modules as $value) {
            $this->out($value);
        }
    }

    /**
     * If the argument 'force' is not set, it will display the trash info about the selected modules
     * @param string $module Module to analyse/clean
     * @return void
     */
    public function clear($module = null)
    {
        if (!$module) {
            $this->out('No module selected');

            return;
        }

        $query = TableRegistry::get($module);

        if (!$query->behaviors()->has('Trash')) {
            // Module don't support trash
            return;
        }

        if ($this->params['dry-run']) {
            $trashEntities = TableRegistry::get($module)->find('onlyTrashed')->count();
            $this->out("The module $module have " . number_format($trashEntities) . " record(s) in the trash.");

            return;
        }

        $age = $this->getDaysConfig();
        $date = new Time($age);
        $count = $query->removeBehavior('Trash')->deleteAll(['trashed <' => $date]);
        Log::write('info', "Clean up $module: " . number_format($count) . " trash record older than $age.");
    }

    /**
     * Clear all trashed records
     * @return void
     */
    public function clearAll()
    {
        $modules = $this->getList();
        foreach ($modules as $module) {
            $this->clear($module);
        }
    }

    /**
     * Get a list of all the CSV Modules
     * @return array
     */
    private function getList()
    {
        $dir = new Folder(Configure::read('CsvMigrations.modules.path'));
        $folders = $dir->read(true)[0];

        return (array)$folders;
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

        $result = Configure::read('Trash.stats.age');
        if ($result) {
            return $result;
        }

        $this->info('Parameter "age" is not set in config/Trash.php');

        throw new RuntimeException('Parameter "age" is not set in config/Trash.php');
    }
}
