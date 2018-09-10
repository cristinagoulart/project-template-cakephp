<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;

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
    public function beforeFind(Event $event, Query $query)
    {
        if (static::FORMAT_PRETTY !== $event->subject()->request->getQuery('format')) {
            $query->contain($this->_getFileAssociations($event->subject()->{$event->subject()->name}));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind(Event $event, Entity $entity)
    {
        $table = $event->subject()->{$event->subject()->name};
        $request = $event->subject()->request;

        $this->_resourceToString($entity);

        if (static::FORMAT_PRETTY === $request->query('format')) {
            $this->_prettify($entity, $table, []);
        } else { // @todo temporary functionality, please see _includeFiles() method documentation.
            $this->_restructureFiles($entity, $table);
        }

        $displayField = $table->displayField();
        $entity->{$displayField} = $entity->get($displayField);
    }
}
