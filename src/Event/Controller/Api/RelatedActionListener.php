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
        /** @var \Cake\Controller\Controller */
        $controller = $event->getSubject();

        /** @var \Psr\Http\Message\ServerRequestInterface&\Cake\Http\ServerRequest */
        $request = $controller->getRequest();

        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $table = $controller->loadModel();

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

        /** @var \Cake\Controller\Controller */
        $controller = $event->getSubject();

        /** @var \Psr\Http\Message\ServerRequestInterface&\Cake\Http\ServerRequest */
        $request = $controller->getRequest();

        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $table = $controller->loadModel();

        // Associated table instance.
        $target = $table->getAssociation($request->getParam('pass.1'))->getTarget();

        foreach ($resultSet as $entity) {
            $this->resourceToString($entity);

            static::FORMAT_PRETTY === $request->getQuery('format') ?
                $this->prettify($entity, $target) :
                $this->attachFiles($entity, $target);

            if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
                $this->attachRelatedMenu($entity, $target, $controller->Auth->user(), [
                    'associationController' => $request->getParam('controller'),
                    'associationName' => $target->getRegistryAlias(),
                    'associationId' => $request->getParam('pass.0'),
                ]);
            }
        }
    }
}
