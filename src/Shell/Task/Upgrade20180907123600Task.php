<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\CsvField;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

/**
 *  This class is responsible for adding database lists defined in modules configuration to the database.
 */
class Upgrade20180907123600Task extends Shell
{
    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('Create Database List records from CSV migrations');

        return $parser;
    }

    /**
     * main() method.
     *
     * @return void
     */
    public function main()
    {
        $modules = Utility::findDirs(Configure::read('CsvMigrations.modules.path'));
        if (empty($modules)) {
            $this->err('No CSV modules found.');

            return;
        }

        $lists = $this->getDatabaseLists($modules);
        if (empty($lists)) {
            $this->info('No database list fields found in the application.');

            return;
        }

        $this->createDatabaseLists($lists);

        $this->success(sprintf('%s completed.', $this->getOptionParser()->getDescription()));
    }

    /**
     * Retrieves database list names for specified modules.
     *
     * @param array $modules Module names
     * @return array
     */
    protected function getDatabaseLists(array $modules)
    {
        $result = [];
        foreach ($modules as $module) {
            $result[$module] = $this->getDatabaseListsByModule($module);
        }

        return array_filter($result);
    }

    /**
     * Get an array of database lists from migrations config.
     *
     * @param string $module Module name
     * @return array
     */
    protected function getDatabaseListsByModule($module)
    {
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $module))->parse();
        $config = json_decode(json_encode($config), true);

        if (empty($config)) {
            return [];
        }

        $result = [];
        foreach ($config as $conf) {
            $field = new CsvField($conf);
            if ('dblist' === $field->getType()) {
                $result[] = $field->getLimit();
            }
        }

        return $result;
    }

    /**
     * Creates database lists records for all relevant fields found in the application.
     *
     * @param array $lists Database lists from all modules
     * @return void
     */
    protected function createDatabaseLists(array $lists)
    {
        foreach ($lists as $moduleLists) {
            $this->createDatabaseListsByModule($moduleLists);
        }
    }

    /**
     * Creates database lists for a specific module.
     *
     * @param array $lists Module relevant database lists
     * @return void
     */
    protected function createDatabaseListsByModule(array $lists)
    {
        $table = TableRegistry::get('CsvMigrations.Dblists');

        foreach ($lists as $list) {
            $count = $table->find('all')
                ->where(['name' => $list])
                ->count();

            if (0 < $count) {
                $this->info(sprintf('Database list record "%s" already exists.', $list));
                continue;
            }

            $entity = $table->newEntity(['name' => $list]);

            if ($table->save($entity)) {
                $this->success(sprintf('Added "%s" to database lists table.', $list));
            }
        }
    }
}
