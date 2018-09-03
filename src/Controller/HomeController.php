<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuItemContainerInterface;
use Menu\MenuBuilder\MenuItemInterface;

/**
 * Home Controller
 *
 *
 * @method \App\Model\Entity\Home[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class HomeController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
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

        throw new ForbiddenException(__('Access control has not been defined'));
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
