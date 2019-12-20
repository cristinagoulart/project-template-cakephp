<?php

namespace App\Event\Controller\Api;

use App\Event\EventName;
use App\ORM\PrettyFormatter;
use App\ORM\RawFormatter;
use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Webmozart\Assert\Assert;

class ViewActionListener extends BaseActionListener
{
    /**
     * Returns a list of all events that the API View endpoint will listen to.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            (string)EventName::API_VIEW_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_VIEW_AFTER_FIND() => 'afterFind',
        ];
    }

    /**
     * beforeFind method.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return void
     */
    public function beforeFind(Event $event, QueryInterface $query): void
    {
        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $prettyFormat = static::FORMAT_PRETTY === $controller->getRequest()->getQuery('format');

        Assert::isInstanceOf($query, Query::class);
        $query->formatResults($prettyFormat ?
            new PrettyFormatter() :
            new RawFormatter()
        );
    }

    /**
     * afterFind method.
     *
     * Handles the following:
     * - Attaches associated files to the entity
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @return void
     */
    public function afterFind(Event $event, EntityInterface $entity): void
    {
        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $request = $controller->getRequest();

        $table = $controller->loadModel();
        Assert::isInstanceOf($table, Table::class);

        if (static::FORMAT_PRETTY !== $request->getQuery('format')) {
            $this->attachFiles($entity, $table);
        }

        /**
         * TODO: seems like obsolete code needs to be tested and removed if not used (might be dealing with virtual fields). Added here: https://github.com/QoboLtd/cakephp-csv-migrations/pull/258
         */
        $entity->set($table->getDisplayField(), $entity->get($table->getDisplayField()));
    }
}
