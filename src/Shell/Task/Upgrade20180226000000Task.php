<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use stdClass;

/**
 *  This class is responsible for migrating many-to-many relations to standalone Modules
 *  and getting rid of "manyToMany" section from the Module(s) config.json file.
 */
class Upgrade20180226000000Task extends Shell
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'CsvMigrations.CsvMigration'
    ];

    /**
     * CSV modules configurations path.
     *
     * @var string
     */
    private $path = '';

    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description(
            'Migration of many-to-many relations to standalone Modules and removal of "manyToMany" config section'
        );

        return $parser;
    }

    /**
     * Main method.
     *
     * @return void
     */
    public function main()
    {
        $this->path = Configure::readOrFail('CsvMigrations.modules.path');
        Utility::validatePath($this->path);
        // remove trailing slash
        $this->path = rtrim($this->path, DS);

        foreach (Utility::findDirs($this->path) as $module) {
            $this->migrateModule($module);
        }

        $this->success(sprintf('%s completed.', $this->getOptionParser()->getDescription()));
    }

    /**
     * Converts data into JSON.
     *
     * @param mixed $data Source file data
     * @return string
     */
    private function toJSON($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Handles iteration of many-to-many modules list and migration initialization.
     *
     * @param string $module Module name
     * @return void
     */
    private function migrateModule(string $module): void
    {
        $data = (new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]))->parse();

        $modules = $data->manyToMany->modules;

        if (empty($modules)) {
            return;
        }

        foreach ($modules as $associated) {
            $this->migrate($module, $associated);
        }

        if (! $this->removeManyToManyConfig($module, $data)) {
            $this->abort(sprintf('Failed modifying %s config.json file', $module));
        }
    }

    /**
     * Executes migration logic.
     *
     * @param string $module Module name
     * @param string $associated Associated module name
     * @return void
     */
    private function migrate(string $module, string $associated): void
    {
        $moduleName = $this->generateModuleName([$module, $associated]);

        if ($this->moduleExists($moduleName)) {
            return;
        }

        if (! $this->createDirectories($this->path . DS . $moduleName)) {
            $this->abort(sprintf('Failed to create module %s directories', $moduleName));
        }

        if (! $this->createConfigJSON($this->path . DS . $moduleName)) {
            $this->abort(sprintf('Failed creating/writing-to %s config.json file', $moduleName));
        }

        if (! $this->createMigrationJSON($this->path . DS . $moduleName, $module, $associated)) {
            $this->abort(sprintf('Failed creating/writing-to %s migration.json file', $moduleName));
        }

        $this->info(sprintf('Many-to-many module %s created successfully', $moduleName));

        // bake CSV migration (sleeping for 1 second to avoid conflicts)
        sleep(1);
        $this->CsvMigration->main($moduleName);
    }

    /**
     * Generates many-to-many module name based on associated modules.
     *
     * @param string[] $modules Modules names
     * @return string
     */
    private function generateModuleName(array $modules): string
    {
        sort($modules);

        return implode('', $modules);
    }

    /**
     * Validates if many-to-many module config directory already exists.
     *
     * @param string $value Module name
     * @return bool
     */
    private function moduleExists(string $value): bool
    {
        return file_exists($this->path . DS . $value);
    }

    /**
     * Creates many-to-many module config directories.
     *
     * @param string $path Module config base path
     * @return bool
     */
    private function createDirectories(string $path): bool
    {
        $folder = new Folder();

        if (! $folder->create($path)) {
            return false;
        }

        if (! $folder->create($path . DS . 'config')) {
            return false;
        }

        if (! $folder->create($path . DS . 'db')) {
            return false;
        }

        return true;
    }

    /**
     * Creates config.json in many-to-many module config directory.
     *
     * @param string $path Module config base path
     * @return bool
     */
    private function createConfigJSON(string $path): bool
    {
        $data = [
            'table' => ['type' => 'relation']
        ];

        $file = new File($path . DS . 'config' . DS . 'config.json', true);

        if (! $file->exists()) {
            return false;
        }

        if (! $file->write($this->toJSON($data))) {
            return false;
        }

        return true;
    }

    /**
     * Creates migration.json in many-to-many module config directory.
     *
     * @param string $path Module config base path
     * @param string $module Module name
     * @param string $associated Associated module name
     * @return bool
     */
    private function createMigrationJSON(string $path, string $module, string $associated): bool
    {
        $data = [
            'id' => [
                'name' => 'id',
                'type' => 'uuid',
                'required' => '1',
                'non-searchable' => null,
                'unique' => null
            ],
            $this->getColumnName($module) => [
                'name' => $this->getColumnName($module),
                'type' => sprintf('related(%s)', $module),
                'required' => '1',
                'non-searchable' => null,
                'unique' => null
            ],
            $this->getColumnName($associated) => [
                'name' => $this->getColumnName($associated),
                'type' => sprintf('related(%s)', $associated),
                'required' => '1',
                'non-searchable' => null,
                'unique' => null
            ],
            'created' => [
                'name' => 'created',
                'type' => 'datetime',
                'required' => null,
                'non-searchable' => null,
                'unique' => null
            ],
            'modified' => [
                'name' => 'modified',
                'type' => 'datetime',
                'required' => null,
                'non-searchable' => null,
                'unique' => null
            ]
        ];

        $file = new File($path . DS . 'db' . DS . 'migration.json', true);

        if (! $file->exists()) {
            return false;
        }

        if (! $file->write($this->toJSON($data))) {
            return false;
        }

        return true;
    }

    /**
     * Generates column name for many-to-many table columns.
     *
     * @param string $module Module name
     * @return string
     */
    private function getColumnName(string $module): string
    {
        return sprintf('%s_id', Inflector::singularize(Inflector::underscore($module)));
    }

    /**
     * Removes manyToMany section from config.json
     *
     * @param string $module Module name
     * @param \stdClass $data Config data
     * @return bool
     */
    private function removeManyToManyConfig(string $module, stdClass $data): bool
    {
        unset($data->manyToMany);

        $file = new File($this->path . DS . $module . DS . 'config' . DS . 'config.json');

        if (! $file->write($this->toJSON($data))) {
            return false;
        }

        return true;
    }
}
