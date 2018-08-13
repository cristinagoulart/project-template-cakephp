<?php

namespace App\Event\Plugin\Menu\View;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemContainerInterface;
use Menu\MenuBuilder\MenuItemFactory;

class DashboardMenuListener implements EventListenerInterface
{
    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems',
        ];
    }

    /**
     * Method that updates the provided Menu to include Dashboard links
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
        if ($name === MENU_MAIN && empty($modules)) {
            $this->addDashboardItems($menu);
            $event->setResult($menu);
        }
    }

    /**
     * Creates the necessary menu items for the Dashboard menu.
     * All newly created items are added to the specified container
     *
     * @param MenuInterface $menu The menu to add the created dashboard menu items.
     */
    private function addDashboardItems(MenuInterface $menu)
    {
        $link = MenuItemFactory::createMenuItem([
            'label' => 'Dashboard',
            'url' => '#',
            'icon' => 'tachometer',
        ]);
        $menu->addMenuItem($link);

        $createLink = MenuItemFactory::createMenuItem([
            'label' => 'Create',
            'url' => '/search/dashboards/add',
            'icon' => 'plus',
            'order' => 999999999
        ]);
        $link->addMenuItem($createLink);

        $this->addDashboardItemsFromTable($link, 10);
    }

    /**
     * Iterates through Dashboard table query and creates a new menu item for each record found
     * The newly created items will be added under the specified Menu container
     *
     * @param MenuItemContainerInterface $container Menu Container
     * @param int $startAt Starting order position
     * @return void
     */
    private function addDashboardItemsFromTable(MenuItemContainerInterface $container, $startAt)
    {
        $table = TableRegistry::get('Search.Dashboards');
        $query = $table->find('all')->order(['name' => 'ASC']);

        /**
         * @var int $i
         * @var EntityInterface $entity
         */
        foreach ($query as $i => $entity) {
            $entityItem = MenuItemFactory::createMenuItem([
                'label' => $entity->get('name'),
                'url' => '/search/dashboards/view/' . $entity->get('id'),
                'icon' => 'tachometer',
                'order' => $startAt + $i,
            ]);
            $container->addMenuItem($entityItem);
        }
    }
}
