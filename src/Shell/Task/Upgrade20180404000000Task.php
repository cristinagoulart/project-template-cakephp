<?php
namespace App\Shell\Task;

use App\Event\Plugin\Search\Model\SearchableFieldsListener;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use RuntimeException;
use Search\Utility\Search;

/**
 *  This class is responsible for creating system searches for all system Modules.
 */
class Upgrade20180404000000Task extends Shell
{
    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription('Create system searches for all system Modules');

        return $parser;
    }

    /**
     * Main method.
     *
     * @return void
     */
    public function main()
    {
        if (! Plugin::loaded('Search')) {
            return;
        }

        EventManager::instance()->on(new SearchableFieldsListener());

        $path = Configure::readOrFail('CsvMigrations.modules.path');
        Utility::validatePath($path);
        $path = rtrim($path, DS);

        foreach (Utility::findDirs($path) as $module) {
            if (! $this->isSearchable($module)) {
                continue;
            }

            if ($this->hasSearch($module)) {
                continue;
            }

            $this->createSearch($module);
        }

        $this->success(sprintf('%s completed.', $this->getOptionParser()->getDescription()));
    }

    /**
     * Validates if provided module is searchable.
     *
     * @param string $module Module name
     * @return bool
     */
    private function isSearchable(string $module): bool
    {
        $migrationConfig = new ModuleConfig(ConfigType::MIGRATION(), $module, null, ['cacheSkip' => true]);
        $config = $migrationConfig->parseToArray();

        if (empty($config)) {
            return false;
        }

        $moduleConfig = new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]);
        $config = $moduleConfig->parseToArray();

        if ('module' !== $config['table']['type']) {
            return false;
        }

        return true;
    }

    /**
     * Validates if provided module has a system search.
     *
     * @param string $module Module name
     * @return bool
     */
    private function hasSearch(string $module): bool
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $query = $table->find()
            ->where(['SavedSearches.model' => $module, 'SavedSearches.system' => true])
            ->limit(1);

        return ! $query->isEmpty();
    }

    /**
     * Creates system search for provided module.
     *
     * @param string $module Module name
     *
     * @throws \RuntimeException when failed to create system search
     *
     * @return \Cake\Datasource\EntityInterface
     */
    private function createSearch(string $module): EntityInterface
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $search = new Search(TableRegistry::getTableLocator()->get($module), $this->getUser());
        $id = $search->create(['system' => true]);

        $entity = $table->get($id);
        $entity = $table->patchEntity($entity, [
            'name' => sprintf('Default %s search', Inflector::humanize(Inflector::underscore($module))),
            'system' => true
        ]);

        if (! $table->save($entity)) {
            throw new RuntimeException(sprintf('Failed to create "%s" system search', $module));
        }

        return $entity;
    }

    /**
     * Get user to attach to system search.
     *
     * @todo We might have multiple superusers, so it's better to get the .env DEV_USER
     * @return mixed[]
     */
    private function getUser(): array
    {
        $result = [];
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $query = $table->find()->where(['is_superuser' => true]);

        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $entity = $query->firstOrFail();

        $result = $entity->toArray();

        return $result;
    }
}
