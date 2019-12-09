<?php

namespace App\Crud\Action;

use App\Search\Manager as SearchManager;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Crud\Action\BaseAction;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\ViewVarTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;

/**
 * Handles 'Search' Crud actions
 */
class SearchAction extends BaseAction
{
    use FindMethodTrait;
    use ViewVarTrait;

    /**
     * Default settings for 'related' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'search',
        'view' => null,
        'viewVar' => null,
        'serialize' => [],
        'api' => [
            'methods' => ['post'],
            'success' => [
                'code' => 200,
            ],
            'error' => [
                'code' => 400,
            ],
        ],
    ];

    /**
     * HTTP POST handler
     *
     * @return void
     */
    protected function _post(): void
    {
        list($finder, ) = $this->_extractFinder();
        $options = SearchManager::getOptionsFromRequest(
            (array)$this->_request()->getData(),
            $this->_request()->getQueryParams()
        );

        // always include primary key into the fields
        $options['fields'] = array_merge((array)$this->_table()->getPrimaryKey(), Hash::get($options, 'fields', []));
        $query = $this->_table()->find($finder, $options);

        $subject = $this->_subject(['success' => true, 'query' => $query]);

        if (! property_exists($subject, 'query')) {
            throw new \InvalidArgumentException('"query" property is required');
        }

        $this->_trigger('beforePaginate', $subject);
        $resultSet = $this->_controller()->paginate($subject->query, [
            'limit' => $this->_request()->getData('limit', 10),
            'page' => $this->_request()->getData('page', 1),
        ]);

        $subject->set(['entities' => SearchManager::formatEntities(
            $resultSet,
            $this->_table(),
            ! array_key_exists('group', $options)
        )]);

        $this->_trigger('afterPaginate', $subject);
        $this->_trigger('beforeRender', $subject);
    }
}
