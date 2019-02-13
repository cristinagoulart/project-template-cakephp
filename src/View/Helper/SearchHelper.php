<?php
namespace App\View\Helper;

use App\Model\Table\UsersTable;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
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

    /**
     * Group by count field
     */
    const GROUP_BY_FIELD = 'total';

    public $helpers = ['Url'];

    private $table = null;
    private $factory = null;

    private $fields = [];
    private $filters = [];
    private $associationLabels = [];

    /**
     * Charts list.
     *
     * @var array
     */
    private $charts = [
        ['type' => 'funnelChart', 'icon' => 'filter'],
        ['type' => 'donutChart', 'icon' => 'pie-chart'],
        ['type' => 'barChart', 'icon' => 'bar-chart']
    ];

    /**
     * Search filters getter.
     *
     * @return mixed[]
     */
    public function getFilters(string $table) : array
    {
        $this->setTable($table);

        $key = $this->table->getAlias();
        if (! empty($this->filters[$key])) {
            return $this->filters[$key];
        }

        $result = $this->getSearchableFields($this->table);

        $labels = $this->getAssociationLabels();
        foreach ($result as $index => $options) {
            unset($result[$index]['input']);
            unset($result[$index]['operators']);

            $group = substr($options['field'], 0, strpos($options['field'], '.'));
            $group = array_key_exists($group, $labels) ? $labels[$group] : $group;

            $result[$index]['group'] = $group;
        }

        usort($result, function ($x, $y) {
            return strcasecmp($x['field'], $y['field']);
        });

        $this->filters[$this->table->getAlias()] = $result;

        return $result;
    }

    /**
     * Method that retrieves target table search display fields.
     *
     * @return array
     */
    public function getDisplayFields(string $table) : array
    {
        $this->setTable($table);

        $result = $this->getBasicSearchFieldsFromSystemSearch();

        if (empty($result)) {
            $result = $this->getBasicSearchFieldsFromView();
        }

        foreach ($result as $key => $value) {
            $result[$key] = $this->table->aliasField($value);
        }

        return $result;
    }

    /**
     * Chart options getter.
     *
     * @return mixed[]
     */
    public function getChartOptions(EntityInterface $entity, string $tableId) : array
    {
        $this->setTable($entity->get('model'));

        list($plugin, $controller) = pluginSplit($entity->get('model'));
        $content = $entity->get('content')['saved'];
        list($prefix, $fieldName) = pluginSplit($content['group_by']);

        $result = [];
        foreach ($this->charts as $chart) {
            $result[] = [
                'chart' => $chart['type'],
                'icon' => $chart['icon'],
                'ajax' => [
                    'url' => Router::url([
                        'prefix' => 'api',
                        'plugin' => $plugin,
                        'controller' => $controller,
                        'action' => 'search'
                    ]),
                    'token' => Configure::read('API.token'),
                    'data' => [
                        'direction' => $content['sort_by_order'],
                        'fields' => [$content['group_by'], $prefix . '.' . self::GROUP_BY_FIELD],
                        'sort' => $content['sort_by_field'],
                        'group_by' => $content['group_by']
                    ],
                    'format' => 'pretty'
                ],
                'options' => [
                    'element' => Inflector::delimit($chart['type']) . '_' . $tableId,
                    'resize' => true,
                    'hideHover' => true,
                    'data' => [],
                    'barColors' => ['#0874c7', '#04645e', '#5661f8', '#8298c1', '#c6ba08', '#07ada3'],
                    'lineColors' => ['#0874c7', '#04645e', '#5661f8', '#8298c1', '#c6ba08', '#07ada3'],
                    'labels' => [Inflector::humanize(self::GROUP_BY_FIELD), Inflector::humanize($fieldName)],
                    'xkey' => [$content['group_by']],
                    'ykeys' => [$prefix . '.' . self::GROUP_BY_FIELD]
                ]
            ];
        }

        return $result;
    }

    /**
     * Table instance setter.
     *
     * @param string $table Table name
     * @return void
     */
    private function setTable(string $table) : void
    {
        $this->table = TableRegistry::get($table);
    }

    /**
     * Returns basic search fields from provided Table's system search.
     *
     * @return string[]
     */
    private function getBasicSearchFieldsFromSystemSearch() : array
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $entity = $table->find('all')
            ->where(['SavedSearches.model' => $this->table->getAlias(), 'SavedSearches.system' => true])
            ->enableHydration(true)
            ->first();

        if (null === $entity) {
            return [];
        }

        return (array)$entity->get('content')['saved']['display_columns'];
    }

    /**
     * Returns basic search fields from provided Table's index View csv fields.
     *
     * @return string[]
     */
    private function getBasicSearchFieldsFromView() : array
    {
        $config = [];
        try {
            list($plugin, $module) = pluginSplit($this->table->getRegistryAlias());
            $mc = new ModuleConfig(ConfigType::VIEW(), $module, 'index');
            $config = $mc->parseToArray();
            $config = ! empty($config['items']) ? $config['items'] : [];
        } catch (\InvalidArgumentException $e) {
            Log::error($e);
        }

        return array_map(function ($value) {
            return $value[0];
        }, $config);
    }

    /**
     * Associations labels getter.
     *
     * @return mixed[]
     */
    private function getAssociationLabels() : array
    {
        if (! empty($this->associationLabels[$this->table->getAlias()])) {
            return $this->associationLabels[$this->table->getAlias()];
        }

        $result = [];
        foreach ($this->table->associations() as $association) {
            if (! in_array($association->type(), self::ASSOCIATION_TYPES)) {
                continue;
            }

            $result[$association->getName()] = Inflector::humanize(implode(', ', (array)$association->getForeignKey()));
        }

        $this->associationLabels[$this->table->getAlias()] = $result;

        return $result;
    }

    /**
     * Method that retrieves target table searchable fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param bool $withAssociated flag for including associations fields
     * @return mixed[]
     */
    private function getSearchableFields(RepositoryInterface $table, bool $withAssociated = true) : array
    {
        list($plugin, $controller) = pluginSplit(App::shortName(get_class($table), 'Model/Table', 'Table'));
        $url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search'];

        if (! (new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
            return [];
        }

        if (! empty($this->fields[$table->getAlias()])) {
            return $this->fields[$table->getAlias()];
        }

        $result = $this->getSearchableFieldsByTable($table);
        if ($withAssociated) {
            $result = array_merge($result, $this->includeAssociated($table));
        }

        $this->fields[$table->getAlias()] = $result;

        return $result;
    }

    /**
     * Searchable fields getter by Table instance.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
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

            foreach ($searchOptions as $searchFieldName => $searchFieldOptions) {
                $searchFieldOptions['field'] = $table->aliasField($searchFieldName);
                $result[$table->aliasField($searchFieldName)] = $searchFieldOptions;
            }
        }

        return $result;
    }

    /**
     * Returns the fields definitions for the provided table.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
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
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
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
                // fetch associated model searchable fields
                $this->getSearchableFields($targetTable, false)
            );
        }

        return $result;
    }
}
