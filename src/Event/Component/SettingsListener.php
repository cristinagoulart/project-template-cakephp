<?php
namespace App\Event\Component;

use App\Settings\DbConfig;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class SettingsListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
             'Controller.startup' => [ 'callable' => 'loadUserSettings', 'priority' => 100]
        ];
    }

    /**
     * Load User settings and overwrite defaults
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    public function loadUserSettings(Event $event): void
    {
        /**
         * @var \App\Controller\SettingsController
         */
        $controller = $event->getSubject();
        if (! $controller->components()->has('Auth')) {
            return;
        }

        if (empty($userId)) {
            return;
        }

        Configure::config('dbconfig', new DbConfig('user', $userId));
        Configure::load('Settings', 'dbconfig', true);
    }
}
