<?php

namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Event\Event;
use Cake\ORM\Entity;

class AddActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_ADD_BEFORE_SAVE() => 'beforeSave',
            (string)EventName::API_ADD_AFTER_SAVE() => 'afterSave',
        ];
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
