<?php
namespace App\Crud\Action;

use Cake\ORM\TableRegistry;
use Crud\Action\BaseAction;

class SchemaAction extends BaseAction
{
    /**
     * Default settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'all',
        'view' => null,
        'viewVar' => null,
        'serialize' => [],
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
        //make a random query
        $query = $this->_table()->find('all');
        $items = $this->_controller()->paginate($query);

        $subject = $this->_subject();
        $subject->set(['success' => true, 'entities' => $items]);

        // whitout that it looks for the view.ctp
        $this->_trigger('beforeRender', $subject);
    }
}
