<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;

class ModuleRelatedListener implements EventListenerInterface
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
        $listens = [MenuName::MODULE_RELATED_ROW];
        if (!in_array($name, $listens)) {
            return;
        }

        // Actions are available only for listing entities
        $entity = $event->getSubject();
        if (!($entity instanceof EntityInterface)) {
            return;
        }

        $request = Router::getRequest();
        $menu->addMenuItem($this->getViewMenuItem($entity, $request));
        $menu->addMenuItem($this->getEditMenuItem($entity, $request));
        $menu->addMenuItem($this->getDeleteMenuItem($entity, $request));
        $menu->addMenuItem($this->getUnlinkMenuItem($entity, $request));

        $event->setResult($menu);
    }

    /**
     * Creates and returns the menu item for the unlink action
     *
     * @param EntityInterface $entity Entity to be viewed
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getUnlinkMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        $url = [
            'prefix' => false,
            'plugin' => $plugin,
            'controller' => $associationController,
            'action' => 'unlink',
            $associationId,
            $associationName,
            $entity->id,
        ];

        $menu[] = [
            'url' => $url,
            'icon' => 'chain-broken',
            'label' => __('Unlink'),
            'dataType' => 'ajax-delete-record',
            'type' => 'postlink_button',
            'confirmMsg' => __(
                'Are you sure you want to unlink {0}?',
                strip_tags($entity->{$displayField})
            ),
            'order' => 40
        ];
    }
}
