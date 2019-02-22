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
        $data = $mc->parse();

        $subject = $this->_subject(['success' => true]);

        $this->_controller()->set('data', $data);
        $this->_trigger('beforeRender', $subject);
    }
}
