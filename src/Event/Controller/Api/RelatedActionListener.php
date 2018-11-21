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
        if (static::FORMAT_PRETTY !== $event->getSubject()->request->getQuery('format')) {
            $query->contain(
                $this->_getFileAssociations($this->getAssociatedTable($event))
            );
        }

        $query->order($this->getOrderClause(
            $event->getSubject()->request,
            $event->getSubject()->{$event->getSubject()->name}
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function afterPaginate(Event $event, ResultSetInterface $resultSet)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event, ResultSetInterface $resultSet): void
    {
        if ($resultSet->isEmpty()) {
            return;
        }

        $table = $this->getAssociatedTable($event);

        foreach ($resultSet as $entity) {
            $this->_resourceToString($entity);
        }

        if (static::FORMAT_PRETTY === $event->getSubject()->request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_prettify($entity, App::shortName(get_class($table), 'Model/Table', 'Table'));
            }
        }

        // @todo temporary functionality, please see _includeFiles() method documentation.
        if (static::FORMAT_PRETTY !== $event->getSubject()->request->getQuery('format')) {
            foreach ($resultSet as $entity) {
                $this->_restructureFiles($entity, $table);
            }
        }

        if ((bool)$event->getSubject()->request->getQuery(static::FLAG_INCLUDE_MENUS)) {
            $this->attachRelatedMenu($resultSet, $table, $event->getSubject()->Auth->user(), [
                'associationController' => $event->getSubject()->request->getParam('controller'),
                'associationName' => $table->getRegistryAlias(),
                'associationId' => $event->getSubject()->request->getParam('pass.0'),
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
        $associationName = $event->getSubject()->request->getParam('pass.1');

        return $event->getSubject()->{$event->getSubject()->name}
            ->association($associationName)
            ->getTarget();
    }
}
