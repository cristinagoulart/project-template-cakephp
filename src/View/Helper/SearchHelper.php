<?php
namespace App\View\Helper;

use App\Model\Table\UsersTable;
use App\Search\Manager as SearchManager;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\View;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use DatabaseLog\Model\Table\DatabaseLogsTable;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Search\Model\Entity\SavedSearch;

final class SearchHelper extends Helper
{
    private const ASSOCIATION_TYPES = ['manyToOne'];

    /**
     * Group by count field.
     */
    const GROUP_BY_FIELD = 'total';

    /**
     * Additional helpers.
     *
     * @var array
     */
    public $helpers = ['Url'];

    /**
     * Table instance.
     *
     * @var \Cake\ORM\Table|null
     */
    private $table = null;

    /**
     * Field handler factory instance.
     *
     * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $factory;

    /**
     * Searchable fields.
     *
     * @var array
     */
    private $fields = [];

    /**
     * Search filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Association labels.
     *
     * @var array
     */
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
     * {@inheritDoc}
     */
    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);

        $this->factory = new FieldHandlerFactory();
    }

    /**
     * Search filters getter.
     *
     * @param string $table Table name
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
     * @param string $table Table name
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
     * @param \Search\Model\Entity\SavedSearch $entity Saved search entity
     * @return mixed[]
     */
    public function getChartOptions(SavedSearch $entity) : array
    {
        if (null === Hash::get($entity->get('content'), 'saved.group_by')) {
            return [];
        }

        $this->setTable($entity->get('model'));

        list(, $groupBy) = pluginSplit(Hash::get($entity->get('content'), 'saved.group_by'));

        $resultSet = $this->table->find(
            'search',
            SearchManager::getOptionsFromRequest([
                'criteria' => Hash::get($entity->get('content'), 'saved.criteria', []),
                'fields' => array_merge((array)$this->table->getPrimaryKey(), [
                    Hash::get($entity->get('content'), 'saved.group_by'),
                    $entity->get('model') . '.' . self::GROUP_BY_FIELD
                ]),
                'sort' => Hash::get($entity->get('content'), 'saved.sort_by_field', false),
                'direction' => Hash::get($entity->get('content'), 'saved.sort_by_order', 'asc'),
                'group_by' => Hash::get($entity->get('content'), 'saved.group_by'),
            ], [])
        )->all();

        $result = [];
        foreach ($this->charts as $chart) {
            $options = [
                'chart' => $chart['type'],
                'icon' => $chart['icon'],
                'options' => [
                    'element' => Inflector::delimit($chart['type']) . '_' . uniqid(),
                    'resize' => true,
                    'hideHover' => true,
                    'data' => [],
                    'barColors' => ['#0874c7', '#04645e', '#5661f8', '#8298c1', '#c6ba08', '#07ada3'],
                    'lineColors' => ['#0874c7', '#04645e', '#5661f8', '#8298c1', '#c6ba08', '#07ada3'],
                    'labels' => [Inflector::humanize(self::GROUP_BY_FIELD), Inflector::humanize($groupBy)],
                    'xkey' => [$groupBy],
                    'ykeys' => [self::GROUP_BY_FIELD]
                ]
            ];

            foreach ($resultSet as $record) {
                $value = $record->get($options['options']['ykeys'][0]);
                $label = $this->factory->renderValue(
                    $this->table,
                    $options['options']['xkey'][0],
                    $record->get($options['options']['xkey'][0])
                );

                switch ($chart['type']) {
                    case 'funnelChart':
                    case 'donutChart':
                        $options['options']['data'][] = ['value' => $value, 'label' => $label];
                        break;
                    case 'barChart':
                        $options['options']['data'][] = [
                            $options['options']['ykeys'][0] => $value,
                            $options['options']['xkey'][0] => $label
                        ];
                        break;
                }
            }

            $result[] = $options;
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
