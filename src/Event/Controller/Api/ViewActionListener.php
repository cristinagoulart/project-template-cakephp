<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Event\Event;

class ViewActionListener extends BaseActionListener
{
    /**
     * Returns a list of all events that the API View endpoint will listen to.
     *
     * @return array
     */
    public function implementedEvents() : array
    {
        return [
            (string)EventName::API_VIEW_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_VIEW_AFTER_FIND() => 'afterFind'
        ];
    }

    /**
     * beforeFind method.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return void
     */
    public function beforeFind(Event $event, QueryInterface $query) : void
    {
        //
    }

    /**
     * afterFind method.
     *
     * Handles the following:
     * - Converts field values of type resource to string
     * - Prettifies field values if relevant format is requested
     * - Attaches associated files to the entity
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @return void
     */
    public function afterFind(Event $event, EntityInterface $entity) : void
    {
        /**
         * @var \Psr\Http\Message\ServerRequestInterface
         */
        $request = $event->getSubject()->request;

        /**
         * @var \Cake\Datasource\RepositoryInterface
         */
        $table = $event->getSubject()->{$event->getSubject()->name};

        $this->resourceToString($entity);

        static::FORMAT_PRETTY === $request->getQuery('format') ?
            $this->prettify($entity, $table) :
            $this->attachFiles($entity, $table);

        /**
         * TODO: seems like obsolete code needs to be tested and removed if not used (might be dealing with virtual fields). Added here: https://github.com/QoboLtd/cakephp-csv-migrations/pull/258
         */
        $entity->set($table->getDisplayField(), $entity->get($table->getDisplayField()));
    }
}
