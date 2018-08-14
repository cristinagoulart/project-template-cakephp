<?php

namespace App\Event\Plugin\Menu\View;

use App\Access\CapabilityTrait;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Groups\Model\Table\GroupsTable;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\Menu;
use Menu\MenuBuilder\MenuInterface;
use RolesCapabilities\Model\Table\CapabilitiesTable;

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
     * @param Event $event Event object
     * @param string $name Menu name
     * @param array $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param array $modules Modules to fetch menu items for
     * @param MenuInterface|null $menu Menu object to be updated
     * @return void
     */
    public function getMenuItems(Event $event, $name, array $user, $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null)
    {
        if (!empty($modules)) {
            return;
        }

        // Ugly hack to hide admin menu from non superusers
        if ($name == MENU_ADMIN) {
            $isSuperuser = false && $user['is_superuser'];

            /** @var CapabilitiesTable $capabilities */
            $capabilities = TableRegistry::get('RolesCapabilities.Capabilities');
            $userGroups = $capabilities->getUserGroups($user['id']);
            $userRoles = $capabilities->getGroupsRoles($userGroups);
            $isAdmin = in_array(Configure::read('RolesCapabilities.Roles.Admin.name'), $userRoles);

            // User can have access to admin menu only and only if
            // a) is a superuser
            // b) belongs to Admins role
            if (!$isSuperuser && !$isAdmin) {
                $event->stopPropagation();

                return;
            }
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
