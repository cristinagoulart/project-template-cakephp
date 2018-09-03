<?php
namespace App\Controller;

use App\Feature\Factory;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuItemContainerInterface;
use Menu\MenuBuilder\MenuItemInterface;

/**
 * System Controller
 */
class SystemController extends AppController
{
    /**
     * Display system information
     *
     * This action displays a variety of useful system
     * information, like project name, URL, version,
     * installed plugins, composer libraries, PHP version,
     * PHP configurations, server environment, etc.
     *
     * @return void
     */
    public function info()
    {
        $tabs = Configure::read('SystemInfo.tabs');
        $this->set('tabs', $tabs);
    }

    /**
     * Error method
     *
     * Default redirect method for loggedin users
     * in case the system throws an error on switched off
     * debug. Otherwise, it'll use native Cake Error pages.
     *
     * @return void
     */
    public function error()
    {
    }

    /**
     * Action responsible for listing all system searches.
     *
     * @return void
     */
    public function searches()
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        $query = $table->find()->where(['system' => true]);

        $entities = [];
        foreach ($query->all() as $entity) {
            $feature = Factory::get('Module' . DS . $entity->get('model'));
            if (! $feature->isActive()) {
                continue;
            }

            $entities[] = $entity;
        }

        $this->set('entities', $entities);
    }

    /**
     * By taking into consideration the main menu, it attempts to dynamically define a home page.
     * Then redirects user to the real home page.
     * Otherwise, it displays an error message explaining what went wrong
     *
     * @return \Cake\Http\Response|null
     */
    public function home()
    {
        // Raise event for main menu
        $event = new Event((string)EventName::GET_MENU_ITEMS(), $this, [
            'name' => MENU_MAIN,
            'user' => $this->Auth->user(),
        ]);
        $this->getEventManager()->dispatch($event);

        if (!empty($event->result)) {
            $firstMenuItem = $this->getFirstMenuItem($event->result);
            if (!empty($firstMenuItem)) {
                return $this->redirect($firstMenuItem->getUrl());
            }
        }
    }

    /**
     * Goes through the provided menu container and attempts to find a menu item that has a URL assigned.
     * Returns the first match, null otherwise.
     *
     * @param MenuItemContainerInterface $container Menu container to be iterated
     * @return MenuItemInterface|null
     */
    public function getFirstMenuItem(MenuItemContainerInterface $container)
    {
        foreach ($container->getMenuItems() as $menuItem) {
            if (!empty($menuItem->getMenuItems())) {
                return $this->getFirstMenuItem($menuItem);
            }

            if (!empty($menuItem->getUrl())) {
                return $menuItem;
            }
        }

        return null;
    }
}
