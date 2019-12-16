<?php

namespace App\Utility;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

final class FieldList
{
    private const TYPE_PATTERN = '/(.*?)\((.*?)\)/';
    private const TYPES = ['list', 'sublist', 'dblist', 'country', 'currency'];

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $field;

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * @param string $model Model name
     * @param string $field Field name
     */
    public function __construct(string $model, string $field)
    {
        Assert::stringNotEmpty($model);
        Assert::stringNotEmpty($field);

        $this->model = $model;
        $this->table = TableRegistry::getTableLocator()->get($model);
        Assert::true($this->table->getSchema()->hasColumn($field));

        $this->field = $field;
    }

    /**
     * @return bool
     */
    public function has(): bool
    {
        $field = new Field($this->model, $this->field);

        if (! in_array($field->type(), self::TYPES, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        $field = $this->field;

        /**
         * Handles the special cases of combined fields, this will go away
         * once we properly separate database column and UI field definitions.
         */
        foreach (['_amount', '_currency', '_unit'] as $item) {
            $strlen = strlen($item);
            if ($item === substr($field, -$strlen, $strlen)) {
                $field = substr($field, 0, strlen($field) - $strlen);
                break;
            }
        }

        $config = (new ModuleConfig(ConfigType::MIGRATION(), $this->model))->parseToArray();

        $type = (string)Hash::get($config, $field . '.type');

        return 1 === preg_match(self::TYPE_PATTERN, $type, $matches) ? $matches[2] : '';
    }

    /**
     * @param mixed[] $flags Flag options
     * @return mixed[]
     */
    public function options(array $flags = []): array
    {
        if (! $this->has()) {
            return [];
        }

        $defaultFlags = ['filter' => true, 'flatten' => true, 'prettify' => true];

        $flags += $defaultFlags;

        $field = new Field($this->model, $this->field);

        if ('dblist' === $field->type()) {
            return $this->getOptionsFromDatabase();
        }

        $options = $this->getOptionsFromFile();

        $options = $this->format($flags, $options);

        if ((bool)$flags['flatten']) {
            $options = self::flatten($options);
        }

        return $options;
    }

    /**
     * @return mixed[]
     */
    private function getOptionsFromDatabase(): array
    {
        $table = TableRegistry::getTableLocator()->get('CsvMigrations.Dblists');
        Assert::isInstanceOf($table, \CsvMigrations\Model\Table\DblistsTable::class);

        $result = [];
        foreach ($table->getOptions($this->name()) as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    private function getOptionsFromFile(): array
    {
        list($module, $name) = false !== strpos($this->name(), '.') ?
            explode('.', $this->name(), 2) :
            [$this->model, $this->name()];

        $options = (new ModuleConfig(ConfigType::LISTS(), $module, $name))->parseToArray();

        return array_key_exists('items', $options) ? $options['items'] : [];
    }

    /**
     * @param mixed[] $flags Flag options
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private function format(array $flags, array $options): array
    {
        array_walk($options, function (&$item, $key) {
            $item = array_merge(['value' => $key], $item);
        }, $options);

        if ((bool)$flags['filter']) {
            $options = self::filter($options);
        }

        if ((bool)$flags['prettify']) {
            $options = $this->prettify($options);
        }

        $options = array_map(function ($item) use ($flags) {
            unset($item['inactive']);
            if (array_key_exists('children', $item)) {
                $item['children'] = $this->format($flags, $item['children']);
            }

            return $item;
        }, $options);

        return array_values($options);
    }

    /**
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private static function filter(array $options): array
    {
        return array_filter($options, function ($item) {
            return true !== $item['inactive'];
        });
    }

    /**
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private function prettify(array $options): array
    {
        $name = $this->name();
        if (false !== strpos($name, '.')) {
            list($name) = explode('.', $name, 2);
        }

        // handles specific list special case, this needs to be abstracted to
        // another concept, probably list options can have an 'icon' property.
        if ('currencies' === $name) {
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

        // handles specific list special case, this needs to be abstracted to
        // another concept, probably list options can have an 'icon' property.
        if ('countries' === $name) {
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
     * @param mixed[] $options List options
     * @return mixed[]
     */
    private static function flatten(array $options): array
    {
        $result = [];
        foreach ($options as $value => $option) {
            $children = [];
            if (array_key_exists('children', $option)) {
                $children = $option['children'];
                unset($option['children']);
            }

            $result[] = $option;
            if ([] !== $children) {
                $result = array_merge($result, self::flatten($children));
            }
        }

        return array_values($result);
    }
}
