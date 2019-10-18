<?php
namespace App\Utility;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

final class Model
{
    private const TYPE_PATTERN = '/(.*?)\((.*?)\)/';
    private const LIST_TYPES = ['list', 'sublist', 'dblist', 'country', 'currency'];
    private const RELATED_TYPE = 'related';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * Constructor method.
     *
     * @param string $model Model name
     */
    private function __construct(string $model)
    {
        Assert::stringNotEmpty($model);

        $this->table = TableRegistry::getTableLocator()->get($model);
    }

    /**
     * Model fields getter.
     *
     * @param string $model Model name
     * @return mixed[]
     */
    public static function fields(string $model) : array
    {
        return (new self($model))->getFields();
    }

    /**
     * Fields getter.
     *
     * @return mixed[]
     */
    private function getFields() : array
    {
        $result = [];
        foreach ($this->table->getSchema()->columns() as $column) {
            $result = array_merge($result, [$this->getField($column)]);
        }

        return array_values(array_filter($result));
    }

    /**
     * Field info getter.
     *
     * @param string $column Column name
     * @return mixed[]
     */
    private function getField(string $column) : array
    {
        $result = [
            'name' => $column,
            'label' => $this->getLabel($column),
            'type' => $this->getType($column),
            'db_type' => $this->table->getSchema()->getColumnType($column),
            'meta' => $this->getMeta($column)
        ];

        if (in_array($result['type'], self::LIST_TYPES, true)) {
            $listName = $this->getListName($column);
            if ('' !== $listName) {
                $result['options'] = 'dblist' === $result['type'] ?
                    $this->getDatabaseListOptions($listName) :
                    $this->getListOptions($listName);
            }
        }

        if (self::RELATED_TYPE === $result['type']) {
            $relatedTableName = $this->getRelatedTableName($column);
            $result['source'] = Inflector::dasherize($relatedTableName);
            $result['display_field'] = TableRegistry::getTableLocator()->get($relatedTableName)->getDisplayField();
        }

        return $result;
    }

    /**
     * Field label getter.
     *
     * @param string $column Column name
     * @return string
     */
    private function getLabel(string $column) : string
    {
        $config = (new ModuleConfig(ConfigType::FIELDS(), $this->getModelName()))->parseToArray();

        $default = substr($column, -3) === '_id' ? substr($column, 0, -3) : $column;
        $default = Inflector::humanize(Inflector::underscore($default));

        return Hash::get($config, $column . '.label', $default);
    }

    /**
     * Field type getter.
     *
     * @param string $column Column name
     * @return string
     */
    private function getType(string $column) : string
    {
        $type = $this->getTypeFromMigration($column);

        return '' !== $type ? $type : (string)$this->table->getSchema()->getColumnType($column);
    }

    /**
     * Migration field type getter.
     *
     * @param string $column Column name
     * @return string
     */
    private function getTypeFromMigration(string $column) : string
    {
        $combinedFields = [
            ['_amount', 'decimal'],
            ['_currency', 'currency'],
            ['_unit', 'list']
        ];

        /**
         * Handles the special cases of combined fields, this will go away once we properly separate database column and UI field definitions.
         */
        foreach ($combinedFields as $combinedField) {
            $strlen = strlen($combinedField[0]);
            if ($combinedField[0] === substr($column, -$strlen, $strlen)) {
                return $combinedField[1];
            }
        }

        $config = (new ModuleConfig(ConfigType::MIGRATION(), $this->getModelName()))->parseToArray();

        $type = Hash::get($config, $column . '.type');
        if (null === $type) {
            return '';
        }

        return 1 === preg_match(self::TYPE_PATTERN, $type, $matches) ? $matches[1] : $type;
    }

    /**
     * Field meta getter.
     *
     * @param string $column Column name
     * @return string[]
     */
    private function getMeta(string $column) : array
    {
        $result = [];
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $this->getModelName()))->parseToArray();

        if (Hash::get($config, $column . '.required')) {
            $result[] = 'required';
        }

        if (Hash::get($config, $column . '.unique')) {
            $result[] = 'unique';
        }

        if (Hash::get($config, $column . '.non-searchable')) {
            $result[] = 'non-searchable';
        }

        return $result;
    }

    /**
     * Related table name getter.
     *
     * @param string $column Column name
     * @return string
     */
    private function getRelatedTableName(string $column) : string
    {
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $this->getModelName()))->parseToArray();

        $type = Hash::get($config, $column . '.type');
        Assert::stringNotEmpty($type);

        preg_match(self::TYPE_PATTERN, $type, $matches);
        Assert::keyExists($matches, 2);
        Assert::stringNotEmpty($matches[2]);

        return $matches[2];
    }

    /**
     * List name getter.
     *
     * @param string $column Column name
     * @return string
     */
    private function getListName(string $column) : string
    {
        /**
         * Handles the special cases of combined fields, this will go away once we properly separate database column and UI field definitions.
         */
        foreach (['_amount', '_currency', '_unit'] as $field) {
            $strlen = strlen($field);
            if ($field === substr($column, -$strlen, $strlen)) {
                $column = substr($column, 0, strlen($column) - $strlen);
                break;
            }
        }

        $config = (new ModuleConfig(ConfigType::MIGRATION(), $this->getModelName()))->parseToArray();

        $type = (string)Hash::get($config, $column . '.type');

        return 1 === preg_match(self::TYPE_PATTERN, $type, $matches) ? $matches[2] : '';
    }

    /**
     * Database list options getter.
     *
     * @param string $listName List name
     * @return mixed[]
     */
    private function getDatabaseListOptions(string $listName) : array
    {
        $table = TableRegistry::getTableLocator()->get('CsvMigrations.Dblists');
        Assert::isInstanceOf($table, \CsvMigrations\Model\Table\DblistsTable::class);

        $result = [];
        foreach ($table->getOptions($listName) as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * List options getter.
     *
     * @param string $listName List name
     * @return mixed[]
     */
    private function getListOptions(string $listName) : array
    {
        list($module, $listName) = false !== strpos($listName, '.') ?
            explode('.', $listName, 2) :
            [$this->getModelName(), $listName];

        try {
            $options = (new ModuleConfig(ConfigType::LISTS(), $module, $listName))->parseToArray();
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        if (! array_key_exists('items', $options)) {
            return [];
        }

        return $this->formatListOptions(
            $this->flattenListOptions($this->filterListOptions($options['items'])),
            $listName
        );
    }

    /**
     * Filters provided list options.
     *
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private function filterListOptions(array $options) : array
    {
        return array_filter($options, function ($item) {
            return $item['inactive'] !== true;
        });
    }

    /**
     * Flattens provided list options.
     *
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private function flattenListOptions(array $options) : array
    {
        $result = [];
        foreach ($options as $value => $extras) {
            $item = ['value' => $value, 'label' => $extras['label']];
            $result[$value] = $item;

            if (array_key_exists('children', $extras)) {
                $result = array_merge($result, $this->flattenListOptions($extras['children']));
            }
        }

        return array_values($result);
    }

    /**
     * Flattens provided list options.
     *
     * @param mixed[] $options List options
     * @param string $listName List name
     * @return mixed[]
     */
    private function formatListOptions(array $options, string $listName) : array
    {
        if ('currencies' === $listName) {
            $result = [];
            foreach ($options as $item) {
                $list = Configure::readOrFail('Currencies.list');
                if (array_key_exists($item['value'], $list)) {
                    $item['label'] = sprintf(
                        '<span title="%s">%s&nbsp;(%s)</span>',
                        $list[$item['value']]['description'],
                        $list[$item['value']]['symbol'],
                        $item['label']
                    );
                }
                $result[] = $item;
            }

            return $result;
        }
        if ('countries' === $listName) {
            return array_map(function ($item) {
                $item['label'] = sprintf(
                    '<span class="flag-icon flag-icon-%s flag-icon-default"></span>&nbsp;&nbsp;%s',
                    strtolower($item['value']),
                    $item['label']
                );

                return $item;
            }, $options);
        }

        return array_map(function ($item) {
            $item['label'] = str_repeat(' - ', substr_count($item['value'], '.')) . $item['label'];

            return $item;
        }, $options);
    }

    /**
     * Model name getter.
     *
     * @return string
     */
    private function getModelName() : string
    {
        $result = pluginSplit(App::shortName(get_class($this->table), 'Model/Table', 'Table'));

        return $result[1];
    }
}
