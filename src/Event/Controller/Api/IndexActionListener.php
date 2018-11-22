<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;

class IndexActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_INDEX_BEFORE_PAGINATE() => 'beforePaginate',
            (string)EventName::API_INDEX_AFTER_PAGINATE() => 'afterPaginate',
            (string)EventName::API_INDEX_BEFORE_RENDER() => 'beforeRender'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforePaginate(Event $event, QueryInterface $query): void
    {
        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        if (static::FORMAT_PRETTY !== $request->getQuery('format')) {
            $query->contain(
                $this->_getFileAssociations($controller->{$controller->getName()})
            );
        }

        $this->filterByConditions($query, $event);

        $query->order($this->getOrderClause(
            $request,
            $controller->{$controller->getName()}
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function afterPaginate(Event $event, ResultSetInterface $resultSet): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event, ResultSetInterface $resultSet): void
    {
        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        if ($resultSet->isEmpty()) {
            return;
        }

        $table = $controller->{$controller->getName()};

        foreach ($resultSet as $entity) {
            $this->_resourceToString($entity);
        }

        if (static::FORMAT_PRETTY === $request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_prettify($entity, $table);
            }
        }

        // @todo temporary functionality, please see _includeFiles() method documentation.
        if (static::FORMAT_PRETTY !== $request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_restructureFiles($entity, $table);
            }
        }

        if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
            $this->attachMenu($resultSet, $table, $controller->Auth->user());
        }
    }

    /**
     * Method that filters ORM records by provided conditions.
     *
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \Cake\Event\Event $event The event
     * @return void
     */
    private function filterByConditions(QueryInterface $query, Event $event): void
    {
        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        if (empty($request->query('conditions'))) {
            return;
        }

        $conditions = [];
        $tableName = $controller->getName();
        foreach ($request->query('conditions') as $k => $v) {
            if (false === strpos($k, '.')) {
                $k = $tableName . '.' . $k;
            }

            $conditions[$k] = $v;
        };

        $query->applyOptions(['conditions' => $conditions]);
    }
}
