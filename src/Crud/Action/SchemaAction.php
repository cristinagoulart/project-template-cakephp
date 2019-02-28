<?php
namespace App\Crud\Action;

use Cake\Core\App;
use Cake\ORM\TableRegistry;
use Crud\Action\BaseAction;
use CsvMigrations\Model\AssociationsAwareTrait;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * Handles 'Schema' Crud actions
 *
 */
class SchemaAction extends BaseAction
{
    /**
     * Default settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'api' => [
            'success' => [
                'code' => 200
            ],
            'error' => [
                'code' => 400
            ]
        ]
    ];

    /**
     * Only GET allowed on HTTP verbs
     *
     * @return void
     */
    protected function _get() : void
    {
        $controller = $this->_controller()->getName();
        $moduleConfig = new ModuleConfig(ConfigType::MIGRATION(), $controller);
        $fields = $moduleConfig->parseToArray();
        $db_fields_type = TableRegistry::getTableLocator()->get($controller)->getSchema()->typeMap();

        $data_fields = [];
        foreach ($fields as $field => $value) {
            if (preg_match('/^related\(/', $value['type'])) {
                preg_match('#\((.*?)\)#', $value['type'], $my_relation);
                $value['association'] = AssociationsAwareTrait::generateAssociationName($controller, $my_relation[1]);
                $value['type'] = 'related';
            }

            if (preg_match('/^list\(/', $value['type'])) {
                preg_match('#\((.*?)\)#', $value['type'], $my_list);
                $list = new ModuleConfig(ConfigType::LISTS(), $controller, $my_list[1]);
                $value['options'] = $this->removeKeys($list->parseToArray()['items']);
            }

            if (preg_match('/^money\(/', $value['type'])) {
                $amount = $value;
                $amount['name'] = $amount['name'] . '_amount';
                $amount['db_type'] = 'decimal';

                $value['name'] = $value['name'] . '_currency';
                $value['db_type'] = 'string';

                $data_fields[] = $value;
                $data_fields[] = $amount;
                continue;
            }

            if (preg_match('/^metric\(/', $value['type'])) {
                $amount = $value;
                $amount['name'] = $amount['name'] . '_amount';
                $amount['db_type'] = 'decimal';

                $value['name'] = $value['name'] . '_unit';
                $value['db_type'] = 'string';

                $data_fields[] = $value;
                $data_fields[] = $amount;
                continue;
            }

            $data_fields[] = $value;
        }

        $data_association = [];
        foreach ($this->_table()->associations() as $association) {
            $data_association[] = [
                'name' => $association->getName(),
                'model' => App::shortName(get_class($association->getTarget()), 'Model/Table', 'Table'),
                'type' => $association->type(),
                'primary_key' => $association->getBindingKey(),
                'foreign_key' => $association->getForeignKey()
            ];
        }

        $subject = $this->_subject(['success' => true]);

        $this->_controller()->set('data', ['fields' => $data_fields, 'associations' => $data_association]);
        $this->_trigger('beforeRender', $subject);
    }

    /**
     * Remove keys from array and nested array.
     * @param  mixed[] $data input data
     * @return mixed[] array with no keys.
     */
    private function removeKeys(array $data) : array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (!empty($value['children'])) {
                $value['children'] = $this->removeKeys($value['children']);
            }
            $result[] = $value;
        }

        return $result;
    }
}
