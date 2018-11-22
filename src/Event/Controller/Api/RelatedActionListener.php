<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Core\App;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;

class RelatedActionListener extends BaseActionListener
{
    /**
     * Returns a list of all events that the API Related endpoint will listen to.
     *
     * @return array
     */
    public function implementedEvents() : array
    {
        return [
            (string)EventName::API_RELATED_BEFORE_PAGINATE() => 'beforePaginate',
            (string)EventName::API_RELATED_AFTER_PAGINATE() => 'afterPaginate',
            (string)EventName::API_RELATED_BEFORE_RENDER() => 'beforeRender'
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
        $request = $event->getSubject()->request;

        /**
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $event->getSubject()->{$event->getSubject()->name};

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
         * @var \Cake\Controller\Controller
         */
        $controller = $event->getSubject();

        /**
         * @var \Psr\Http\Message\ServerRequestInterface
         */
        $request = $controller->getRequest();

        /**
         * Associated table instance.
         *
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $controller->{$controller->name}
            ->association($request->getParam('pass.1'))
            ->getTarget();

        foreach ($resultSet as $entity) {
            $this->resourceToString($entity);

            static::FORMAT_PRETTY === $request->getQuery('format') ?
                $this->prettify($entity, $table) :
                $this->attachFiles($entity, $table);

            if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
                $this->attachRelatedMenu($entity, $table, $controller->Auth->user(), [
                    'associationController' => $request->getParam('controller'),
                    'associationName' => $table->getRegistryAlias(),
                    'associationId' => $request->getParam('pass.0'),
                ]);
            }
        }
    }
}
