<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;

class EditActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_EDIT_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_EDIT_AFTER_FIND() => 'afterFind',
            (string)EventName::API_EDIT_BEFORE_SAVE() => 'beforeSave',
            (string)EventName::API_EDIT_AFTER_SAVE() => 'afterSave'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFind(Event $event, Query $query): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind(Event $event, Entity $entity): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, Entity $entity): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(Event $event, Entity $entity): void
    {
        //
    }
}
