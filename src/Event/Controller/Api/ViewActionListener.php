<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Hash;

class ViewActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_VIEW_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_VIEW_AFTER_FIND() => 'afterFind'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFind(Event $event, Query $query): void
    {
        if (static::FORMAT_PRETTY !== $event->getSubject()->request->getQuery('format')) {
            $query->contain($this->_getFileAssociations($event->getSubject()->{$event->getSubject()->getName()}));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind(Event $event, Entity $entity): void
    {
        $table = $event->getSubject()->{$event->getSubject()->getName()};
        $request = $event->getSubject()->request;

        $this->_resourceToString($entity);

        if (static::FORMAT_PRETTY === Hash::get($request->getQueryParams(), 'format', '')) {
            $this->_prettify($entity, $table, []);
        } else { // @todo temporary functionality, please see _includeFiles() method documentation.
            $this->_restructureFiles($entity, $table);
        }

        $displayField = $table->getDisplayField();
        $entity->{$displayField} = $entity->get($displayField);
    }
}
