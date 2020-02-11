<?php

namespace App\Controller;

use App\Feature\Factory;
use App\Menu\MenuName;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuItemContainerInterface;

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
     * @return \Cake\Http\Response|void|null
     */
    public function info()
    {
        $tabs = Configure::read('SystemInfo.tabs');
        $this->set('tabs', $tabs);
    }

    /**
     * Action responsible for listing all system searches.
     *
     * @return \Cake\Http\Response|void|null
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
     * @return \Cake\Http\Response|void|null
     */
    public function home()
    {
        // Raise event for main menu
        $event = new Event((string)EventName::GET_MENU_ITEMS(), $this, [
            'name' => MenuName::MAIN,
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
     * @param \Menu\MenuBuilder\MenuItemContainerInterface $container Menu container to be iterated
     * @return \Menu\MenuBuilder\MenuItemInterface|null
     */
    public function getFirstMenuItem(MenuItemContainerInterface $container): ?\Menu\MenuBuilder\MenuItemInterface
    {
        foreach ($container->getMenuItems() as $menuItem) {
            if (!$menuItem->isEnabled()) {
                continue;
            }

            if (!empty($menuItem->getMenuItems())) {
                return $this->getFirstMenuItem($menuItem);
            }

            if (!empty($menuItem->getUrl())) {
                return $menuItem;
            }
        }

        return null;
    }

    /**
     * Overwrites default access checking to provide access to homepage.
     * User have access to home action only and only if is already logged in.
     *
     * @param array $url Url to be checked
     * @param array $user Current user, if any
     * @return bool
     */
    protected function _checkAccess(array $url, array $user): bool
    {
        if (empty($user) && $url['action'] === 'home') {
            return false;
        }

        return parent::_checkAccess($url, $user);
    }
}
