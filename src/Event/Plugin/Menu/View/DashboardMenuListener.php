<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemContainerInterface;
use Menu\MenuBuilder\MenuItemFactory;
use Search\Model\Table\DashboardsTable;
use Webmozart\Assert\Assert;

class DashboardMenuListener implements EventListenerInterface
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
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems',
        ];
    }

    /**
     * Method that updates the provided Menu to include Dashboard links
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
        /**
         * @var \Menu\MenuBuilder\MenuInterface $menu
         */
        $menu = $menu;

        if ($name === MenuName::MAIN && empty($modules)) {
            $this->addAdminMenuItems($menu, $user);
            $event->setResult($menu);

            return;
        }

        if ($name === MenuName::DASHBOARD_VIEW) {
            /**
             * @var \Cake\Http\ServerRequest $request
             */
            $request = Router::getRequest();
            $entity = $event->getSubject() instanceof EntityInterface ? $event->getSubject() : null;

            if (! is_null($entity)) {
                $menu->addMenuItem($this->getEditMenuItem($entity, $request));
                $menu->addMenuItem($this->getDeleteMenuItem($entity, $request));
            }

            $event->setResult($menu);

            return;
        }
    }

    /**
     * Creates the necessary menu items for the Dashboard menu.
     * All newly created items are added to the specified container
     *
     * @param \Menu\MenuBuilder\MenuInterface $menu The menu to add the created dashboard menu items.
     * @param mixed[] $user Current user
     * @return void
     */
    private function addAdminMenuItems(MenuInterface $menu, array $user): void
    {
        $link = MenuItemFactory::createMenuItem([
            'label' => __('Dashboards'),
            'url' => '#',
            'icon' => 'tachometer',
        ]);
        $menu->addMenuItem($link);

        $this->addDashboardItemsFromTable($link, $user, 10);

        $createUrl = empty($link->getMenuItems()) ? '/search/dashboards/index' : '/search/dashboards/add';
        $createLink = MenuItemFactory::createMenuItem([
            'label' => __('Create'),
            'url' => $createUrl,
            'icon' => 'plus',
            'order' => PHP_INT_MAX,
        ]);
        $link->addMenuItem($createLink);
    }

    /**
     * Iterates through Dashboard table query and creates a new menu item for each record found
     * The newly created items will be added under the specified Menu container
     *
     * @param \Menu\MenuBuilder\MenuItemContainerInterface $container Menu Container
     * @param mixed[] $user Current user
     * @param int $startAt Starting order position
     * @return void
     */
    private function addDashboardItemsFromTable(MenuItemContainerInterface $container, array $user, int $startAt): void
    {
        $table = TableRegistry::get('Search.Dashboards');
        Assert::isInstanceOf($table, DashboardsTable::class);

        $dashboardOrder = Configure::read('dashboard_menu_order_value');

        $query = $table->getUserDashboards($user);
        Assert::isInstanceOf($query, QueryInterface::class);

        /**
         * @var int $i
         * @var \Cake\Datasource\EntityInterface $entity
         */
        foreach ($query->all() as $i => $entity) {
            $order = $startAt;
            if (isset($dashboardOrder[$entity->get('id')])) {
                $order = $startAt + $dashboardOrder[$entity->get('id')];
            }

            $entityItem = MenuItemFactory::createMenuItem([
                'label' => $entity->get('name'),
                'url' => '/search/dashboards/view/' . $entity->get('id'),
                'icon' => 'tachometer',
                'order' => $order,
            ]);

            $container->addMenuItem($entityItem);
        }
    }
}
