<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Controller\Controller;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Webmozart\Assert\Assert;

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
        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $request = $controller->getRequest();

        $table = $controller->loadModel();
        Assert::isInstanceOf($table, Table::class);

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

        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $request = $controller->getRequest();

        $table = $controller->loadModel();
        Assert::isInstanceOf($table, Table::class);

        foreach ($resultSet as $entity) {
            $this->resourceToString($entity);

            static::FORMAT_PRETTY === $request->getQuery('format') ?
                $this->prettify($entity, $table) :
                $this->attachFiles($entity, $table);

            if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
                $this->attachMenu($entity, $table, $controller->Auth->user());
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
        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $request = $controller->getRequest();

        $table = $controller->loadModel();
        Assert::isInstanceOf($table, Table::class);

        $queryParam = Hash::get($request->getQueryParams(), 'conditions', []);
        if (empty($queryParam)) {
            return;
        }

        $conditions = [];
        foreach ($queryParam as $field => $value) {
            $key = $table->aliasField($field);
            if (is_array($value)) {
                $key .= ' IN';
            }
            $conditions[$key] = $value;
        };

        $query->applyOptions(['conditions' => $conditions]);
    }
}
