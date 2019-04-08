<?php
namespace App\Utility;

use App\Model\Table\UsersTable;
use App\Search\Manager;
use Cake\Core\App;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use DatabaseLog\Model\Table\DatabaseLogsTable;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Search\Model\Entity\SavedSearch;
use Webmozart\Assert\Assert;

final class Search
{
    /**
     * Supported association types.
     */
    const ASSOCIATION_TYPES = ['manyToOne'];

    /**
     * Default sql order by direction
     */
    const DEFAULT_SORT_BY_ORDER = 'desc';

    /**
     * Default sql aggregator
     */
    const DEFAULT_AGGREGATOR = 'AND';

    /**
     * Group by count field.
     */
    const GROUP_BY_FIELD = 'total';

    /**
     * Searchable fields.
     *
     * @var array
     */
    private static $fields = [];

    /**
     * Search filters.
     *
     * @var array
     */
    private static $filters = [];

    /**
     * Association labels.
     *
     * @var array
     */
    private static $associationLabels = [];

    /**
     * Charts list.
     *
     * @var array
     */
    const CHARTS = [
        ['type' => 'funnelChart', 'icon' => 'filter', 'class' => '\Search\Widgets\Reports\DonutChartReportWidget'],
        ['type' => 'pie', 'icon' => 'pie-chart', 'class' => '\Search\Widgets\Reports\PieChartReportWidget'],
        ['type' => 'bar', 'icon' => 'bar-chart', 'class' => '\Search\Widgets\Reports\BarChartReportWidget']
    ];

    /**
     * Search filters getter.
     *
     * @param string $tableName Table name
     * @return mixed[]
     */
    public static function getFilters(string $tableName) : array
    {
        if (! empty(static::$filters[$tableName])) {
            return static::$filters[$tableName];
        }

        $table = TableRegistry::getTableLocator()->get($tableName);
        $labels = self::getAssociationLabels($table);
        $result = self::getSearchableFields($table);
        foreach ($result as $index => $options) {
            unset($result[$index]['input']);
            unset($result[$index]['operators']);

            list($group, ) = pluginSplit($options['field']);
            $group = array_key_exists($group, $labels) ? $labels[$group] : $group;

            $result[$index]['group'] = $group;
        }

        usort($result, function ($x, $y) {
            return strcasecmp($x['field'], $y['field']);
        });

        static::$filters[$tableName] = $result;

        return $result;
    }

    /**
     * Method that retrieves target table search display fields.
     *
     * @param string $tableName Table name
     * @return string[]
     */
    public static function getDisplayFields(string $tableName) : array
    {
        $table = TableRegistry::getTableLocator()->get($tableName);

        $result = self::getDisplayFieldsFromSystemSearch($tableName);

        if (empty($result)) {
            $result = self::getDisplayFieldsFromView($tableName);
        }

        foreach ($result as $key => $value) {
            $result[$key] = $table->aliasField($value);
        }

        return $result;
    }

    /**
     * Chart options getter.
     *
     * @param \Search\Model\Entity\SavedSearch $entity Saved search entity
     * @return mixed[]
     */
    public static function getChartOptions(SavedSearch $entity) : array
    {
        if (null === Hash::get($entity->get('content'), 'saved.group_by')) {
            return [];
        }

        $table = TableRegistry::getTableLocator()->get($entity->get('model'));
        $factory = new FieldHandlerFactory();

        list(, $groupBy) = pluginSplit(Hash::get($entity->get('content'), 'saved.group_by'));

        $resultSet = $table->find('search', Manager::getOptionsFromRequest([
            'criteria' => Hash::get($entity->get('content'), 'saved.criteria', []),
            'fields' => array_merge((array)$table->getPrimaryKey(), [
                Hash::get($entity->get('content'), 'saved.group_by'),
                $entity->get('model') . '.' . self::GROUP_BY_FIELD
            ]),
            'sort' => Hash::get($entity->get('content'), 'saved.sort_by_field', false),
            'direction' => Hash::get($entity->get('content'), 'saved.sort_by_order', 'asc'),
            'group_by' => Hash::get($entity->get('content'), 'saved.group_by'),
        ], []))->all();

        // prettify data
        foreach ($resultSet as $record) {
            $record->set(self::GROUP_BY_FIELD, $record->get(self::GROUP_BY_FIELD));
            $record->set($groupBy, $factory->renderValue($table, $groupBy, $record->get($groupBy)));
        }

        $result = [];
        foreach (self::CHARTS as $chart) {
            $options = [
                'icon' => $chart['icon'],
                'id' => Inflector::delimit($chart['type']) . '_' . uniqid(),
                'chart' => $chart['type']
            ];

            switch ($chart['type']) {
                case 'bar':
                case 'pie':
                    $widget = new $chart['class'];
                    $widget->setConfig([
                        'info' => [
                            'columns' => implode(',', [self::GROUP_BY_FIELD, $groupBy]),
                            'x_axis' => $groupBy,
                            'y_axis' => self::GROUP_BY_FIELD,
                        ]
                    ]);

                    $options += $widget->getChartData($resultSet->toArray());
                    break;
                case 'funnelChart':
                    $data = [];
                    foreach ($resultSet as $record) {
                        $data[] = ['value' => $record->get(self::GROUP_BY_FIELD), 'label' => $record->get($groupBy)];
                    }

                    $options += [
                        'options' => [
                            'resize' => true,
                            'hideHover' => true,
                            'labels' => [Inflector::humanize(self::GROUP_BY_FIELD), Inflector::humanize($groupBy)],
                            'xkey' => [$groupBy],
                            'ykeys' => [self::GROUP_BY_FIELD],
                            'dataChart' => [
                                'type' => $chart['type'],
                                'data' => $data
                            ]
                        ]
                    ];

                    break;
            }

            $result[] = $options;
        }

        return $result;
    }

    /**
     * Associations labels getter.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function getAssociationLabels(Table $table) : array
    {
        if (! empty(self::$associationLabels[$table->getRegistryAlias()])) {
            return self::$associationLabels[$table->getRegistryAlias()];
        }

        $result = [];
        foreach ($table->associations() as $association) {
            if (! in_array($association->type(), self::ASSOCIATION_TYPES)) {
                continue;
            }

            $result[$association->getName()] = Inflector::humanize(implode(', ', (array)$association->getForeignKey()));
        }

        self::$associationLabels[$table->getRegistryAlias()] = $result;

        return $result;
    }

    /**
     * Method that retrieves target table searchable fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param bool $withAssociated flag for including associations fields
     * @return mixed[]
     */
    private static function getSearchableFields(Table $table, bool $withAssociated = true) : array
    {
        list($plugin, $controller) = pluginSplit(App::shortName(get_class($table), 'Model/Table', 'Table'));
        $url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search'];

        if (! (new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
            return [];
        }

        if (! empty(self::$fields[$table->getRegistryAlias()])) {
            return self::$fields[$table->getRegistryAlias()];
        }

        $result = self::getSearchableFieldsByTable($table);
        if ($withAssociated) {
            $result = array_merge($result, self::includeAssociated($table));
        }

        self::$fields[$table->getRegistryAlias()] = $result;

        return $result;
    }

    /**
     * Returns display fields from provided Table's system search.
     *
     * @param string $tableName Table name
     * @return string[]
     */
    private static function getDisplayFieldsFromSystemSearch(string $tableName) : array
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $entity = $table->find('all')
            ->where(['SavedSearches.model' => $tableName, 'SavedSearches.system' => true])
            ->enableHydration(true)
            ->first();

        Assert::nullOrIsInstanceOf($entity, SavedSearch::class);

        if (null === $entity) {
            return [];
        }

        return (array)Hash::get($entity->get('content'), 'saved.display_columns', []);
    }

    /**
     * Returns display fields from provided Table's index View fields.
     *
     * @param string $tableName Table name
     * @return string[]
     */
    private static function getDisplayFieldsFromView(string $tableName) : array
    {
        $config = [];

        list($plugin, $module) = pluginSplit($tableName);
        $mc = new ModuleConfig(ConfigType::VIEW(), $module, 'index');

        try {
            $config = $mc->parseToArray();
            $config = ! empty($config['items']) ? $config['items'] : [];
        } catch (\InvalidArgumentException $e) {
            Log::error($e->getMessage());
        }

        return array_map(function ($value) {
            return $value[0];
        }, $config);
    }

    /**
     * Searchable fields getter by Table instance.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function getSearchableFieldsByTable(Table $table) : array
    {
        $fields = self::getFieldsDefinitionsByTable($table);
        if (empty($fields)) {
            return [];
        }

        $factory = new FieldHandlerFactory();

        $result = [];
        foreach ($fields as $field) {
            $searchOptions = $factory->getSearchOptions($table, $field);
            if (empty($searchOptions)) {
                continue;
            }

            foreach ($searchOptions as $searchFieldName => $searchFieldOptions) {
                $searchFieldName = $table->aliasField((string)$searchFieldName);
                $searchFieldOptions['field'] = $searchFieldName;
                $result[$searchFieldName] = $searchFieldOptions;
            }
        }

        return $result;
    }

    /**
     * Returns the fields definitions for the provided table.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function getFieldsDefinitionsByTable(Table $table) : array
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
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function includeAssociated(Table $table) : array
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
                self::getSearchableFields($targetTable, false)
            );
        }

        return $result;
    }
}
