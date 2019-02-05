<?php
namespace App\Crud\Action;

use App\Search\Manager as SearchManager;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
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

    private $factory = null;

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
                'code' => 200
            ],
            'error' => [
                'code' => 400
            ]
        ]
    ];

    /**
     * HTTP POST handler
     *
     * @return void
     */
    protected function _post() : void
    {
        list($finder) = $this->_extractFinder();
        $options = SearchManager::getOptionsFromRequest(
            $this->_request()->getData(),
            $this->_request()->getQueryParams()
        );

        // always include primary key into the fields
        $options['fields'] = array_merge((array)$this->_table()->getPrimaryKey(), Hash::get($options, 'fields', []));
        $query = $this->_table()->find($finder, $options);

        $subject = $this->_subject(['success' => true, 'query' => $query]);

        $this->_trigger('beforePaginate', $subject);
        $items = $this->_controller()->paginate($subject->query, [
            'limit' => $this->_request()->getData('limit', 10),
            'page' => $this->_request()->getData('page', 1)
        ]);

        $result = [];
        foreach ($items as $entity) {
            $row = $this->formatEntity($entity, $this->_table());
            $row['_permissions'] = $this->getPermissions($entity->get($this->_table()->getPrimaryKey()));
            $result[] = $row;
        }

        $subject->set(['entities' => $result]);

        $this->_trigger('afterPaginate', $subject);
        $this->_trigger('beforeRender', $subject);
    }

    /**
     * Method that formats search result-set entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\Datasource\RepositoryInterface|string $table Table instance
     * @return mixed[]
     */
    private function formatEntity(EntityInterface $entity, RepositoryInterface $table) : array
    {
        if (null === $this->factory) {
            $this->factory = new FieldHandlerFactory();
        }

        $result = [];
        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            // current table field
            if ('_matchingData' !== $field) {
                $result[$table->aliasField($field)] = $this->factory->renderValue($table, $field, $entity->get($field));
                continue;
            }

            foreach ($entity->get('_matchingData') as $associationName => $relatedEntity) {
                $result = array_merge($result, self::formatEntity(
                    $relatedEntity,
                    $table->getAssociation($associationName)->getTarget()
                ));
            }
        }

        return $result;
    }

    /**
     * Returns entity access permissions.
     *
     * @param string $id Entity ID
     * @return mixed[]
     */
    private function getPermissions(string $id) : array
    {
        list($plugin, $controller) = pluginSplit($this->_table()->getAlias());

        $url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id];
        $result['view'] = (new AccessFactory())->hasAccess($url, User::getCurrentUser());
        // if ((new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
        //     $result['view'] = Router::url($url, true);
        // }

        $url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id];
        $result['edit'] = (new AccessFactory())->hasAccess($url, User::getCurrentUser());
        // if ((new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
        //     $result['edit'] = Router::url($url, true);
        // }

        $url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $id];
        $result['delete'] = (new AccessFactory())->hasAccess($url, User::getCurrentUser());
        // if ((new AccessFactory())->hasAccess($url, User::getCurrentUser())) {
        //     $result['delete'] = Router::url($url, true);
        // }

        return $result;
    }
}
