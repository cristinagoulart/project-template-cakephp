<?php

namespace App\Event\Plugin\Menu\View;

use App\Access\CapabilityTrait;
use App\Menu\MenuName;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\Menu;
use Menu\MenuBuilder\MenuInterface;

class ApplicationMenuListener implements EventListenerInterface
{
    use CapabilityTrait;

    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => [
                'callable' => 'getMenuItems',
                'priority' => 1
            ]
        ];
    }

    /**
     * Method that returns menu nested array based on provided menu name
     *
     * @param \Cake\Event\Event $event Event object
     * @param string $name Menu name
     * @param mixed[] $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param mixed[] $modules Modules to fetch menu items for
     * @param \Menu\MenuBuilder\MenuInterface|null $menu Menu object to be updated
     * @return void
     */
    public function getMenuItems(Event $event, string $name, array $user, bool $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null): void
    {
        if (!empty($modules)) {
            return;
        }

        // Only administrators can access the admin menu
        if ($name === MenuName::ADMIN && !$user['is_admin']) {
            $event->stopPropagation();

            return;
        }

        // We are creating the Menu within the listener to be backwards compatible with MenuListener
        if ($menu === null) {
            $menu = new Menu();

            // Update event data to include the newly create menu instance
            $event->setData([
                $name,
                $user,
                $fullBaseUrl,
                $modules,
                $menu,
            ]);
        }

        $event->setResult($menu);
    }
}
