<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * DblistsAdd shell command.
 *
 * Adding dblists(<list_name>) records into dblists table
 * in case the record doesn't exist.
 */
class Upgrade20180907123600Task extends Shell
{
    protected $modules;

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
        $modules = $this->_findCsvModules();

        $this->modules = $modules;

        if (empty($this->modules)) {
            $this->out("<warning>Couldn't find CSV modules to parse</warning>");
            exit();
        }

        $dblists = [];

        foreach ($modules as $k => $module) {
            $lists = $this->_getDbListsFromCsv($module);

            if (!empty($lists)) {
                $dblists[$module] = $lists;
            }
        }

        $table = TableRegistry::get('Dblists');

        if (empty($dblists)) {
            $this->out("<info>No dblist fields found in the application.</info>");
            exit();
        }

        foreach ($dblists as $module => $lists) {
            foreach ($lists as $list) {
                $record = $table->find()
                    ->where(['name' => $list])->first();

                if (!empty($record)) {
                    $this->out("<info>Dblist record [$list] already exists in the dblists table</info>");
                    continue;
                }

                $dblistEntity = $table->newEntity();
                $dblistEntity->name = $list;
                $dblistEntity->created = date('Y-m-d H:i:s', time());
                $dblistEntity->modified = date('Y-m-d H:i:s', time());

                if ($table->save($dblistEntity)) {
                    $this->out("<success>Added [$list] to dblists table</success>");
                }
            }
        }

        $this->success(sprintf('%s completed.', $this->getOptionParser()->getDescription()));
    }

    /**
     * Get an array of dblists from migrations config
     *
     * @param string $module where to look for dblists
     * @return array $result containing indexed array of dblists
     */
    protected function _getDbListsFromCsv($module = null)
    {
        $result = [];

        if (empty($module)) {
            return $result;
        }

        $mc = new ModuleConfig(ConfigType::MIGRATION(), $module);
        $fields = json_decode(json_encode($mc->parse()), true);

        if (empty($fields)) {
            return $result;
        }

        foreach ($fields as $field) {
            if (preg_match('/^dblist\((.+)\)$/', $field['type'], $matches)) {
                $result[] = $matches[1];
            }
        }

        return $result;
    }

    /**
     * Find the list of CSV modules
     *
     * @return array List of modules
     */
    protected function _findCsvModules()
    {
        $result = [];

        $path = Configure::read('CsvMigrations.modules.path');
        if (!is_readable($path)) {
            throw new \RuntimeException("[$path] is not readable");
        }
        if (!is_dir($path)) {
            throw new \RuntimeException("[$path] is not a directory");
        }

        foreach (new \DirectoryIterator($path) as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $result[] = $fileinfo->getFilename();
        }
        asort($result);

        return $result;
    }
}
