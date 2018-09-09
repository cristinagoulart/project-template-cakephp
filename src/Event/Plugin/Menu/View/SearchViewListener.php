<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;

class SearchViewListener implements EventListenerInterface
{
    use MenuEntityTrait;

    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems'
        ];
    }

    /**
     * Method that returns the menu for Search View
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
        $listens = [MenuName::SEARCH_VIEW, MenuName::MODULE_INDEX_ROW];
        if (!in_array($name, $listens)) {
            return;
        }

        // Actions are available only for searching entities
        $entity = $event->getSubject();
        if (!($entity instanceof EntityInterface)) {
            return;
        }

        $request = Router::getRequest();
        $menu->addMenuItem($this->getViewMenuItem($entity, $request));
        $menu->addMenuItem($this->getEditMenuItem($entity, $request));
        $deleteMenuItem = $this->getDeleteMenuItem($entity, $request, true);
        $deleteMenuItem->setViewElement('Search.Menu/search-view-actions-delete', [
            'menuItem' => $deleteMenuItem
        ]);
        $menu->addMenuItem($deleteMenuItem);

        $event->setResult($event);
    }
}
