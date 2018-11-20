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
        $parser->addSubcommand('gc', [
            'help' => 'Clean all records',
            'parser' => [
                'options' => [
                    'age' => [
                        'help' => __('Time to delete (ex: -1 day / -1 months)'),
                        'required' => false,
                        'short' => 'a'
                    ]
                ]
            ],
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return null Success or error code.
     */
    public function gc()
    {
        $age = $this->getDaysConfig();
        // Delete before this day
        $date = new Time($age);
        // list all the Modules
        $dir = new Folder(Configure::read('CsvMigrations.modules.path'));
        $folders = $dir->read(true)[0];
        foreach ($folders as $module) {
            $query = TableRegistry::get($module);

            if (!$query->behaviors()->has('Trash')) {
                // Module don't support trash
                continue;
            }

            try {
                $trashEntities = TableRegistry::get($module)->find('onlyTrashed');
            } catch (\BadMethodCallException $e) {
                $this->out("$module has not trash entities");
                continue;
            }

            $count = $query->removeBehavior('Trash')->deleteAll(['trashed <' => $date]);
        }

        Log::write('info', "Clean up " . number_format($count) . " trash record older than $age.");

        return null;
    }

    /**
     * Get stats log configuration
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getDaysConfig(): string
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
