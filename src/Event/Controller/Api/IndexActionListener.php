<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;

class IndexActionListener extends BaseActionListener
{
    /**
     * Returns a list of all events that the API Index endpoint will listen to.
     *
     * @return array
     */
    public function implementedEvents() : array
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
    public function beforePaginate(Event $event, QueryInterface $query) : void
    {
        /**
         * @var \Psr\Http\Message\ServerRequestInterface
         */
        $request = $event->getSubject()->getRequest();

        /**
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $event->getSubject()->{$event->getSubject()->name};

        $this->filterByConditions($query, $event);

        $query->order($this->getOrderClause($request, $table));
    }

    /**
     * {@inheritDoc}
     */
    public function afterPaginate(Event $event, ResultSetInterface $resultSet) : void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event, ResultSetInterface $resultSet) : void
    {
        if ($resultSet->isEmpty()) {
            return;
        }

        /**
         * @var \Psr\Http\Message\ServerRequestInterface
         */
        $request = $event->getSubject()->request;

        /**
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $event->getSubject()->{$event->getSubject()->name};

        foreach ($resultSet as $entity) {
            $this->resourceToString($entity);

            static::FORMAT_PRETTY === $request->getQuery('format') ?
                $this->prettify($entity, $table) :
                $this->attachFiles($entity, $table);

            if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
                $this->attachMenu($entity, $table, $event->getSubject()->Auth->user());
            }
        }
    }

    /**
     * Method that filters ORM records by provided conditions.
     *
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \Cake\Event\Event $event The event
     * @return void
     */
    private function filterByConditions(QueryInterface $query, Event $event) : void
    {
        /**
         * @var \Psr\Http\Message\ServerRequestInterface
         */
        $request = $event->getSubject()->getRequest();

        /**
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $event->getSubject()->{$event->getSubject()->name};

        if (empty($request->query('conditions'))) {
            return;
        }

        $conditions = [];
        foreach ($request->query('conditions') as $field => $value) {
            $conditions[$table->aliasField($field)] = $value;
        };

        $query->applyOptions(['conditions' => $conditions]);
    }
}
