<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;

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

        $controller = $request->param('controller');
        $id_search = $request->param('pass')[0];

        // @todo move all this in singolar method
        if ($this->isTrashedFilter($id_search, $controller)) {
            $delete = MenuItemFactory::createMenuItem([
                'url' => [
                    'plugin' => false,
                    'controller' => 'soft-delete',
                    'action' => 'delete',
                    $entity->getSource(),
                    $entity->id
                ],
                'label' => __('Permissions'),
                'icon' => 'shield',
            ]);
            $restore = MenuItemFactory::createMenuItem([
                'url' => [
                    'plugin' => false,
                    'controller' => 'soft-delete',
                    'action' => 'restore',
                    $entity->getSource(),
                    $entity->id
                ],
                'label' => __('Permissions'),
                'icon' => 'recycle',
            ]);

            $menu->addMenuItem($delete);
            $menu->addMenuItem($restore);
            $event->setResult($event);

            return;
        }

        $menu->addMenuItem($this->getViewMenuItem($entity, $request));
        $editMenuItem = $this->getEditMenuItem($entity, $request);
        $editMenuItem->disableIf(function () use ($request) {
            return 'Logs' === $request->param('controller');
        });

        $menu->addMenuItem($editMenuItem);
        $deleteMenuItem = $this->getDeleteMenuItem($entity, $request, true);
        $deleteMenuItem->setViewElement('Search.Menu/search-view-actions-delete', [
            'menuItem' => $deleteMenuItem
        ]);

        $deleteMenuItem->disableIf(function () use ($request) {
            return 'Logs' === $request->param('controller');
        });

        $menu->addMenuItem($deleteMenuItem);

        $event->setResult($event);
    }

    /**
     * Check if is used the "trashed" filter.
     * @param  string  $id         The id of the search in SavedSearches table
     * @param  string  $controller The controller name
     * @return bool                True if is trashed exists
     */
    private function isTrashedFilter($id, $controller)
    {
        $query = TableRegistry::get('SavedSearches')->find('all')->where(['id' => $id])->first();

        return in_array($controller . ".trashed", array_keys(json_decode($query->content, true)['saved']['criteria']));
    }
}
