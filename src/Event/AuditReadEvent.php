<?php

namespace App\Event;

use AuditStash\Event\BaseEvent;

class AuditReadEvent extends BaseEvent
{
    /**
     * Returns the type name of this event object.
     *
     * @return string
     */
    public function getEventType()
    {
        return 'read';
    }
}
