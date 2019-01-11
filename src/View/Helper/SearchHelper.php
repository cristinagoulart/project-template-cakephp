<?php
namespace App\View\Helper;

use App\Model\Table\UsersTable;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use DatabaseLog\Model\Table\DatabaseLogsTable;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;

final class SearchHelper extends Helper
{
    private const ASSOCIATION_TYPES = ['manyToOne'];

    private $table;
    private $entity = null;

    private $filters = [];
    private $associationLabels = [];

    private $displayColumns = [];
    private $availableColumns = [];
    private $getGroupColumns = [];
    private $factory = null;
    private $fields = [];
    private $savedSearches = [];
    private $preSaveId = '';

    public function initialize(array $config) : void
    {
        if (! isset($config['table']) || ! $config['table'] instanceof RepositoryInterface) {
            throw new \InvalidArgumentException('Table instance is required.');
        }

        $this->table = $config['table'];
        $this->entity = TableRegistry::get('Search.SavedSearches')
            ->find('all')
            ->enableHydration(true)
            ->where(['id' => $config['id']])
            ->first();
    }

    public function getSavedSearch() : ?EntityInterface
    {
        return $this->entity;
    }

    public function getSavedSearches() : array
    {
        if (! empty($this->savedSearches)) {
            return $this->savedSearches;
        }

        $this->savedSearches = TableRegistry::get('Search.SavedSearches')->find('all')
            ->where([
                'SavedSearches.name IS NOT' => null,
                'SavedSearches.system' => false,
                'SavedSearches.user_id' => Hash::get(User::getCurrentUser(), 'id'),
                'SavedSearches.model' => $this->table->getAlias()
            ])
            ->toArray();

        return $this->savedSearches;
    }

    public function getSearchData() : array
    {
        return Hash::get($this->entity->get('content'), 'latest', []);
    }

    public function getPreSaveId() : string
    {
        if ('' !== $this->preSaveId) {
            return $this->preSaveId;
        }

        $table = TableRegistry::get('Search.SavedSearches');
        $entity = $table->newEntity();
    }

    public function reset(): void
    {
        $table = TableRegistry::get('Search.SavedSearches');

        $entity = $this->entity;
        $content = $entity->get('content');
        $content['latest'] = $content['saved'];
        $table->patchEntity($entity, ['content' => $content]);

        $table->save($entity);
    }

    public function getFilters() : array
    {
        if (! empty($this->filters)) {
            return $this->filters;
        }

        foreach ($this->getFields() as $field => $options) {
            $this->filters[$field] = $options['label'];
        }

        ksort($this->filters);

        $this->filters = $this->groupFilters($this->filters);

        return $this->filters;
    }

    public function getDisplayColumns() : array
    {
        if (! empty($this->displayColumns)) {
            debug('here');

            return $this->displayColumns;
        }

        $setColumns = Hash::get($this->entity->get('content'), 'latest.display_columns', []);

        foreach ($this->getFields() as $field => $options) {
            if (in_array($field, $setColumns)) {
                $this->displayColumns[$field] = $options['label'] . $this->getColumnSuffix($field);
            }
        }

        // sort display columns based on saved search display_columns order
        $this->displayColumns = array_merge(array_flip($setColumns), $this->displayColumns);

        return $this->displayColumns;
    }

    public function getAvailableColumns() : array
    {
        if (! empty($this->availableColumns)) {
            debug('here');

            return $this->availableColumns;
        }

        $setColumns = Hash::get($this->entity->get('content'), 'latest.display_columns', []);

        foreach ($this->getFields() as $field => $options) {
            if (! in_array($field, $setColumns)) {
                $this->availableColumns[$field] = $options['label'] . $this->getColumnSuffix($field);
            }
        }

        asort($this->availableColumns);

        return $this->availableColumns;
    }

    public function getGroupColumns() : array
    {
        if (! empty($this->getGroupColumns)) {
            return $this->getGroupColumns;
        }

        $setColumns = Hash::get($this->entity->get('content'), 'latest.display_columns', []);

        foreach ($this->getFields() as $field => $options) {
            $tableName = substr($field, 0, strpos($field, '.'));
            if ($this->entity->get('model') === $tableName) {
                $this->getGroupColumns[$field] = $options['label'] . $this->getColumnSuffix($field);
            }
        }

        asort($this->getGroupColumns);

        return $this->getGroupColumns;
    }

    /**
     * Associations labels getter.
     *
     * @return mixed[]
     */
    public function getAssociationLabels() : array
    {
        if (! empty($this->associationLabels)) {
            return $this->associationLabels;
        }

        $result = [];
        foreach ($this->table->associations() as $association) {
            if (! in_array($association->type(), self::ASSOCIATION_TYPES)) {
                continue;
            }

            $result[$association->getName()] = Inflector::humanize(implode(', ', (array)$association->getForeignKey()));
        }

        $this->associationLabels = $result;

        return $this->associationLabels;
    }

    /**
     * Method that retrieves target table searchable fields.
     *
     * @return mixed[]
     */
    public function getFields(bool $withAssociated = true) : array
    {
        return $this->getSearchableFields($this->table, $withAssociated);
    }

    /**
     * Column suffix generator.
     *
     * Generates suffixes for associated columns, used mostly for display purposes.
     *
     * Sample suffix: ' (Author Id)'
     *
     * @param string $field Aliased field name
     * @return string
     */
    private function getColumnSuffix(string $field) : string
    {
        $labels = $this->getAssociationLabels();

        $tableName = substr($field, 0, strpos($field, '.'));
        $tableName = array_key_exists($tableName, $labels) ? $labels[$tableName] : $tableName;

        return $this->entity->get('model') !== $tableName ? sprintf(' (%s)', $tableName) : '';
    }

    private function groupFilters(array $filters) : array
    {
        $labels = $this->getAssociationLabels();

        $result = [];
        foreach ($filters as $field => $label) {
            $group = substr($field, 0, strpos($field, '.'));
            $group = array_key_exists($group, $labels) ? $labels[$group] : $group;

            $result[$group][$field] = $label;
        }

        foreach ($result as $model => $modelFilters) {
            asort($modelFilters);
            $result[$model] = $modelFilters;
        }
        ksort($result);
        // dd($result);

        // push current model fields to the top of the filters list
        // foreach ($result as $model => $modelFilters) {
        //     if ($this->table->getAlias() === $model) {
        //         $result = array_merge($modelFilters, $result);
        //         unset($result[$model]);
        //     }
        // }

        return $result;
    }

    /**
     * Method that retrieves target table searchable fields.
     *
     * @return mixed[]
     */
    private function getSearchableFields(RepositoryInterface $table, bool $withAssociated = true) : array
    {
        list($plugin, $controller) = pluginSplit(App::shortName(get_class($this->table), 'Model/Table', 'Table'));
        $url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search'];

        if (! (new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
            return [];
        }

        if (! empty($this->fields[$table->getAlias()])) {
            return $this->fields[$table->getAlias()];
        }

        $this->fields[$table->getAlias()] = $this->getSearchableFieldsByTable($table);
        if ($withAssociated) {
            $this->fields[$table->getAlias()] = array_merge(
                $this->fields[$table->getAlias()],
                $this->includeAssociated($table)
            );
        }

        return $this->fields[$table->getAlias()];
    }

    /**
     * Searchable fields getter by Table instance.
     *
     * @return mixed[]
     */
    private function getSearchableFieldsByTable(RepositoryInterface $table) : array
    {
        $fields = static::getFieldsDefinitionsByTable($table);
        if (empty($fields)) {
            return [];
        }

        if (null === $this->factory) {
            $this->factory = new FieldHandlerFactory();
        }

        $result = [];
        foreach ($fields as $field) {
            $searchOptions = $this->factory->getSearchOptions($table, $field);
            if (empty($searchOptions)) {
                continue;
            }

            $options = [];
            foreach ($searchOptions as $k => $v) {
                $options[$table->aliasField($k)] = $v;
            }
            $result = array_merge($result, $options);
        }

        return $result;
    }

    /**
     * Returns the fields definitions for the provided table.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table to retrieve fields for
     * @return mixed[]
     */
    private function getFieldsDefinitionsByTable(RepositoryInterface $table) : array
    {
        if ($table instanceof UsersTable) {
            return ['first_name', 'last_name', 'username', 'email', 'created', 'modified'];
        }

        if ($table instanceof DatabaseLogsTable) {
            return ['hostname', 'ip', 'uri', 'message', 'type', 'created'];
        }

        list(, $module) = pluginSplit(App::shortName(get_class($table), 'Model/Table', 'Table'));

        $config = (new ModuleConfig(ConfigType::MIGRATION(), $module))->parseToArray();

        return array_keys($config);
    }

    /**
     * Get associated tables searchable fields.
     *
     * @return mixed[]
     */
    private function includeAssociated(RepositoryInterface $table) : array
    {
        $result = [];

        foreach ($table->associations() as $association) {
            // skip non-supported associations
            if (! in_array($association->type(), self::ASSOCIATION_TYPES)) {
                continue;
            }

            $targetTable = $association->getTarget();

            // skip associations with itself
            if ($targetTable->getTable() === $table->getTable()) {
                continue;
            }

            $result = array_merge(
                $result,
                $this->getSearchableFields($targetTable, false) // fetch associated model searchable fields
            );
        }

        return $result;
    }
}
