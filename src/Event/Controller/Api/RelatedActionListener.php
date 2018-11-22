<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Core\App;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;

class RelatedActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
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
    public function beforePaginate(Event $event, QueryInterface $query): void
    {
        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        if (static::FORMAT_PRETTY !== $request->getQuery('format')) {
            /**
             * @var \Cake\ORM\Query $query
             */
            $query = $query;
            $query->contain(
                $this->_getFileAssociations($this->getAssociatedTable($event))
            );
        }

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

        $table = $this->getAssociatedTable($event);

        foreach ($resultSet as $entity) {
            $this->_resourceToString($entity);
        }

        if (static::FORMAT_PRETTY === $request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_prettify($entity, App::shortName(get_class($table), 'Model/Table', 'Table'));
            }
        }

        // @todo temporary functionality, please see _includeFiles() method documentation.
        if (static::FORMAT_PRETTY !== $request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_restructureFiles($entity, $table);
            }
        }

        if ((bool)$request->getQuery(static::FLAG_INCLUDE_MENUS)) {
            $this->attachRelatedMenu($resultSet, $table, $controller->Auth->user(), [
                'associationController' => $request->getParam('controller'),
                'associationName' => $table->getRegistryAlias(),
                'associationId' => $request->getParam('pass.0'),
            ]);
        }
    }

    /**
     * Retrieves association's target table.
     *
     * @param \Cake\Event\Event $event Event object
     * @return \Cake\Datasource\RepositoryInterface
     */
    private function getAssociatedTable(Event $event): RepositoryInterface
    {
        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        $associationName = $request->getParam('pass.1');

        return $controller->{$controller->getName()}
            ->getAssociation($associationName)
            ->getTarget();
    }
}
