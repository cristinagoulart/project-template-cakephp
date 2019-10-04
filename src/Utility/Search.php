<?php
namespace App\Utility;

use App\Model\Table\UsersTable;
use App\Search\Manager;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Log\Log;
use Cake\ORM\Association;
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
use Search\Aggregate\AggregateInterface;
use Search\Model\Entity\SavedSearch;
use Search\Service\Search as SearchService;
use Webmozart\Assert\Assert;

final class Search
{
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

    const SUPPORTED_ASSOCIATIONS = [
        Association::MANY_TO_ONE,
        Association::MANY_TO_MANY,
        Association::ONE_TO_MANY
    ];

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
     * @param \Search\Model\Entity\SavedSearch $savedSearch Saved search entity
     * @return mixed[]
     */
    public static function getChartOptions(SavedSearch $savedSearch) : array
    {
        $aggregate = array_filter((array)$savedSearch->get('fields'), function ($item) {
            return 1 === preg_match(AggregateInterface::AGGREGATE_PATTERN, $item);
        });

        if ([] === $aggregate) {
            return [];
        }

        preg_match(AggregateInterface::AGGREGATE_PATTERN, array_values($aggregate)[0], $matches);
        $aggregate = $matches[0];
        $aggregateType = $matches[1];
        $aggregateFieldAliased = $matches[2];
        list(, $aggregateField) = pluginSplit($aggregateFieldAliased);

        $table = TableRegistry::getTableLocator()->get($savedSearch->get('model'));
        $factory = new FieldHandlerFactory();

        $query = $table->find('search', Manager::getOptionsFromRequest([
            'criteria' => $savedSearch->get('criteria'),
            'fields' => $savedSearch->get('fields'),
            'conjunction' => $savedSearch->get('conjunction'),
            'sort' => $savedSearch->get('order_by_field'),
            'direction' => $savedSearch->get('order_by_direction'),
            'group_by' => $savedSearch->get('group_by')
        ], []));

        $rowLabel = sprintf('%s (%s)', $aggregateField, $aggregateType);
        list(, $rowValue) = $savedSearch->get('group_by') ? pluginSplit($savedSearch->get('group_by')) : ['', $aggregateField];
        $filters = self::getFilters($savedSearch->get('model'));
        $rows = [];
        foreach ($query->all() as $entity) {
            $formatted = self::formatEntity($entity, $table, $factory);

            $row = [$rowLabel => $formatted[$aggregate]];
            $key = array_search($aggregateFieldAliased, array_column($filters, 'field'));
            $row[$rowValue] = $savedSearch->get('group_by') ? $formatted[$savedSearch->get('group_by')] : $filters[$key]['label'];

            $rows[] = $row;
        }

        $result = [];
        foreach (self::CHARTS as $chart) {
            $options = [
                'icon' => $chart['icon'],
                'id' => Inflector::delimit($chart['type']) . '_' . uniqid(),
                'chart' => $chart['type'],
                'slug' => $savedSearch->get('name')
            ];

            switch ($chart['type']) {
                case 'bar':
                case 'pie':
                    $widget = new $chart['class'];
                    $widget->setConfig([
                        'modelName' => $savedSearch->get('model'),
                        'info' => [
                            'columns' => implode(',', [$rowLabel, $rowValue]),
                            'x_axis' => $rowValue,
                            'y_axis' => $rowLabel
                        ]
                    ]);

                    $widget->setContainerId($options);
                    $options += $widget->getChartData($rows);
                    break;
                case 'funnelChart':
                    $data = [];
                    foreach ($rows as $row) {
                        $data[] = [
                            'value' => Hash::get($row, $rowLabel),
                            'label' => Hash::get($row, $rowValue)
                        ];
                    }

                    $options += [
                        'options' => [
                            'resize' => true,
                            'hideHover' => true,
                            'labels' => [
                                Inflector::humanize($rowLabel),
                                Inflector::humanize($rowValue)
                            ],
                            'xkey' => [$rowValue],
                            'ykeys' => [$rowLabel],
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
     * Method that formats search result-set entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @param \CsvMigrations\FieldHandlers\FieldHandlerFactory $factory Field handler factory instance
     * @return mixed[]
     */
    private static function formatEntity(EntityInterface $entity, Table $table, FieldHandlerFactory $factory) : array
    {
        $result = [];
        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            // current table field
            if ('_matchingData' !== $field) {
                $result[$table->aliasField($field)] = 1 === preg_match(AggregateInterface::AGGREGATE_PATTERN, $field) ?
                    $entity->get($field) :
                    $factory->renderValue($table, $field, $entity->get($field));
                continue;
            }

            foreach ($entity->get('_matchingData') as $associationName => $relatedEntity) {
                $result = array_merge($result, self::formatEntity(
                    $relatedEntity,
                    $table->getAssociation($associationName)->getTarget(),
                    $factory
                ));
            }
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
            if (! in_array($association->type(), self::SUPPORTED_ASSOCIATIONS)) {
                continue;
            }

            $result[$association->getName()] = sprintf(
                '%s (%s)',
                App::shortName(get_class($association->getTarget()), 'Model/Table', 'Table'),
                Inflector::humanize(implode(', ', (array)$association->getForeignKey()))
            );
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

        return (array)$entity->get('fields');
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
            if (! in_array($association->type(), self::SUPPORTED_ASSOCIATIONS)) {
                continue;
            }

            $targetTable = $association->getTarget();

            // skip associations with itself
            if ($targetTable->getTable() === $table->getTable()) {
                continue;
            }

            // fetch associated model searchable fields
            $fields = self::getSearchableFields($targetTable, false);

            $fields = array_map(function ($item) use ($association) {
                $item['association'] = $association->type();

                return $item;
            }, $fields);

            $result = array_merge($result, $fields);
        }

        return $result;
    }
}
