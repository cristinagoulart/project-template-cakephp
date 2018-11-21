<?php
namespace App\Event\Plugin\Search\Model;

use App\Model\Table\UsersTable;
use Cake\Core\App;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use DatabaseLog\Model\Table\DatabaseLogsTable;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RolesCapabilities\Access\AccessFactory;
use Search\Event\EventName;

class SearchableFieldsListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_SEARCH_SEARCHABLE_FIELDS() => 'getSearchableFields',
            (string)EventName::MODEL_SEARCH_BASIC_SEARCH_FIELDS() => 'getBasicSearchFields',
            (string)EventName::MODEL_SEARCH_DISPLAY_FIELDS() => 'getDisplayFields'
        ];
    }

    /**
     * Method that retrieves target table searchable fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return void
     */
    public function getSearchableFields(Event $event, RepositoryInterface $table, array $user)
    {
        list($plugin, $controller) = pluginSplit(App::shortName(get_class($table), 'Model/Table', 'Table'));
        $url = [
            'plugin' => $plugin,
            'controller' => $controller,
            'action' => 'search'
        ];

        $accessFactory = new AccessFactory();
        if (! $accessFactory->hasAccess($url, $user)) {
            return;
        }

        $event->setResult(static::getSearchableFieldsByTable($table, $user));
    }

    /**
     * Searchable fields getter by Table instance.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @param bool $withAssociated Flag for including associated searchable fields
     * @return array
     */
    public static function getSearchableFieldsByTable(RepositoryInterface $table, array $user, $withAssociated = true)
    {
        $factory = new FieldHandlerFactory();
        $fields = static::getFieldsDefinitionsByTable($table);
        $result = [];
        if (empty($fields)) {
            return $result;
        }

        if ($user['is_admin'] && $table->hasField('trashed') && !empty($fields)) {
            $fields[] = 'trashed';
        }

        foreach ($fields as $field) {
            $field_definitions = [];
            if ('trashed' == $field) {
                $field_definitions = [
                    'fieldDefinitions' => [
                        'type' => 'datetime',
                        'name' => 'trashed'
                    ]
                ];
            }

            $searchOptions = $factory->getSearchOptions($table, $field, $field_definitions);

            if (empty($searchOptions)) {
                continue;
            }

            $options = [];
            foreach ($searchOptions as $k => $v) {
                $options[$table->aliasField($k)] = $v;
            }
            $result = array_merge($result, $options);
        }

        if ($withAssociated) {
            $result = array_merge($result, static::byAssociations($table, $user));
        }

        return $result;
    }

    /**
     * Returns the fields definitions for the provided table
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table to retrieve fields for
     * @return array
     */
    private static function getFieldsDefinitionsByTable(RepositoryInterface $table)
    {
        $fields = [];
        if ($table instanceof UsersTable) {
            return ['first_name', 'last_name', 'username', 'email', 'created', 'modified'];
        }

        if ($table instanceof DatabaseLogsTable) {
            return ['hostname', 'ip', 'uri', 'message', 'type', 'created'];
        }

        $method = 'getFieldsDefinitions';
        // skip if method cannot be called
        if (!method_exists($table, $method) || !is_callable([$table, $method])) {
            return $fields;
        }

        $fields = $table->{$method}();
        if (empty($fields)) {
            return [];
        }

        $fields = array_keys($fields);

        return $fields;
    }

    /**
     * Get associated tables searchable fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return array
     */
    private static function byAssociations(RepositoryInterface $table, array $user)
    {
        $result = [];
        foreach ($table->associations() as $association) {
            // skip non-supported associations
            if (!in_array($association->type(), ['manyToOne'])) {
                continue;
            }

            $targetTable = $association->getTarget();

            // skip associations with itself
            if ($targetTable->getTable() === $table->getTable()) {
                continue;
            }

            // fetch associated model searchable fields
            $searchableFields = static::getSearchableFieldsByTable($targetTable, $user, false);
            if (empty($searchableFields)) {
                continue;
            }

            $result = array_merge($result, $searchableFields);
        }

        return $result;
    }

    /**
     * Method that retrieves target table basic search fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return void
     */
    public function getBasicSearchFields(Event $event, RepositoryInterface $table)
    {
        $result = $this->getBasicSearchFieldsFromConfig($table);

        if (empty($result)) {
            $result = $this->getBasicSearchFieldsFromSystemSearch($table);
        }

        if (empty($result)) {
            $result = $this->getBasicSearchFieldsFromView($table);
        }

        foreach ($result as $key => $value) {
            $result[$key] = $table->aliasField($value);
        }

        $event->setResult($result);
    }

    /**
     * Method that retrieves target table search funcionality display fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return void
     */
    public function getDisplayFields(Event $event, RepositoryInterface $table)
    {
        $result = $this->getBasicSearchFieldsFromSystemSearch($table);

        if (empty($result)) {
            $result = $this->getBasicSearchFieldsFromView($table);
        }

        foreach ($result as $key => $value) {
            $result[$key] = $table->aliasField($value);
        }

        $event->setResult($result);
    }

    /**
     * Returns basic search fields from provided Table's configuration.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    private function getBasicSearchFieldsFromConfig(RepositoryInterface $table)
    {
        $config = [];
        try {
            $mc = new ModuleConfig(ConfigType::MODULE(), $table->registryAlias());
            $config = $mc->parse();
            $config = json_decode(json_encode($config), true);
        } catch (InvalidArgumentException $e) {
            Log::error($e);
        }

        $result = [];
        if (!empty($config['table']['basic_search_fields'])) {
            $result = array_filter(array_map('trim', $config['table']['basic_search_fields']), 'strlen');
        }

        return $result;
    }

    /**
     * Returns basic search fields from provided Table's system search.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    private function getBasicSearchFieldsFromSystemSearch(RepositoryInterface $table)
    {
        $entity = TableRegistry::getTableLocator()->get('Search.SavedSearches')->find()
            ->where(['SavedSearches.model' => $table->getAlias(), 'SavedSearches.system' => true])
            ->first();

        if (is_null($entity)) {
            return [];
        }

        $searchData = json_decode($entity->content);

        return (array)$searchData->saved->display_columns;
    }

    /**
     * Returns basic search fields from provided Table's index View csv fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    private function getBasicSearchFieldsFromView(RepositoryInterface $table)
    {
        $config = [];
        try {
            list($plugin, $module) = pluginSplit($table->registryAlias());
            $mc = new ModuleConfig(ConfigType::VIEW(), $module, 'index');
            $config = $mc->parse();
            $config = !empty($config->items) ? json_decode(json_encode($config->items), true) : [];
        } catch (InvalidArgumentException $e) {
            Log::error($e);
        }

        if (empty($config)) {
            return [];
        }

        $result = [];
        foreach ($config as $column) {
            $result[] = $column[0];
        }

        return $result;
    }
}
