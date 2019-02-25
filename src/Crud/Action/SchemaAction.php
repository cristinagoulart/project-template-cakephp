<?php
namespace App\Crud\Action;

use Cake\ORM\TableRegistry;
use Crud\Action\BaseAction;
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
        $mc = new ModuleConfig(ConfigType::MIGRATION(), $controller);
        $fields = (array)$mc->parse();
        $db_fields_type = TableRegistry::getTableLocator()->get($controller)->getSchema()->typeMap();

        $data_fields = [];
        foreach ($fields as $field => $value) {
            $value->{'db_type'} = preg_match('/^(money|metric)/', $value->type) ? 'integer': $db_fields_type[$value->name];
            $data_fields[] = $value;
        }

        $data_association = [];
        foreach ($this->_table()->associations() as $association) {
            $data_association[] = [
                'name' => $association->getName(),
                'model' => $association->getTarget()->getTable(),
                'type' => $association->type(),
                'primary_key' => $association->getBindingKey(),
                'foreign_key' => $association->getForeignKey()
            ];
        }

        $subject = $this->_subject(['success' => true]);

        $this->_controller()->set('data', ['fields' => $data_fields, 'association' => $data_association]);
        $this->_trigger('beforeRender', $subject);
    }
}
