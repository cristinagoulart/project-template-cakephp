<?php

namespace App\Event\Plugin\Menu\View;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;

class SearchViewListener implements EventListenerInterface
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
        if ($name !== 'search_view') {
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
        $menu->addMenuItem($this->getDeleteMenuItem($entity, $request));

        $event->setResult($event);
    }

    /**
     * Creates and returns the menu item for the view action
     *
     * @param EntityInterface $entity Entity to be viewed
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getViewMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id],
            'icon' => 'eye',
            'label' => __('View'),
            'type' => 'link_button',
            'order' => 10
        ]);
    }

    /**
     * Creates and returns the menu item for the edit action
     *
     * @param EntityInterface $entity Entity to be edited
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getEditMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id],
            'icon' => 'pencil',
            'label' => __('Edit'),
            'type' => 'link_button',
            'order' => 20
        ]);
    }

    /**
     * Creates and returns the menu item for the delete action
     *
     * @param EntityInterface $entity Entity to be deleted
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getDeleteMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        $table = TableRegistry::get($entity->getSource());
        $displayField = $table->getDisplayField();
        $displayName = $entity->has($displayField) ? $entity->get($displayField) : null;

        return MenuItemFactory::createMenuItem([
            'url' => ['prefix' => 'api', 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', '_ext' => 'json', $id],
            'icon' => 'trash',
            'label' => __('Delete'),
            'dataType' => 'ajax-delete-record',
            'type' => 'link_button',
            'confirmMsg' => __(
                'Are you sure you want to delete {0}?',
                empty(trim($displayName)) ? 'this record' : strip_tags($displayName)
            ),
            'order' => 30
        ]);
    }
}
