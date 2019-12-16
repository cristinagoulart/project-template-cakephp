<?php

namespace App\Event\Plugin\CsvMigrations\Model;

use ArrayObject;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use CsvMigrations\Model\Table\DblistItemsTable;
use CsvMigrations\Model\Table\DblistsTable;

class DatabaseListsListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => [
                'callable' => 'beforeFind',
                'priority' => 1,
            ],
        ];
    }

    /**
     * Skip access check for Database lists.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\ORM\Query $query Query object
     * @param \ArrayObject $options Query options
     * @param bool $primary Flag for Table primary query
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, ?bool $primary): void
    {
        if (! in_array(get_class($event->getSubject()), [DblistItemsTable::class, DblistsTable::class])) {
            return;
        }

        $options['accessCheck'] = false;
    }
}
