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

class ModuleIndexRowListener implements EventListenerInterface
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
     * Method that returns the menu for Search View
     *
     * @param Event $event Event object
     * @param string $name Menu name
     * @param mixed[] $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param mixed[] $modules Modules to fetch menu items for
     * @param \Menu\MenuBuilder\MenuInterface|null $menu Menu object to be updated
     *
     * @return void
     */
    public function getMenuItems(Event $event, string $name, array $user, bool $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null): void
    {
        if (! $menu instanceof MenuInterface) {
            return;
        }

        if (MenuName::MODULE_INDEX_ROW !== $name) {
            return;
        }

        $entity = $event->getSubject();
        if (! $entity instanceof EntityInterface) {
            return;
        }

        $request = Router::getRequest();
        if (! $request instanceof ServerRequest) {
            return;
        }

        $menu->addMenuItem($this->getViewMenuItem($entity, $request));
        $editMenuItem = $this->getEditMenuItem($entity, $request);
        $editMenuItem->disableIf(function () use ($request) {
            return 'Logs' === $request->getParam('controller');
        });
        $menu->addMenuItem($editMenuItem);

        $deleteMenuItem = $this->getDeleteMenuItem($entity, $request, true);
        $deleteMenuItem->setViewElement('Plugin/Menu/view-actions-delete', [
            'menuItem' => $deleteMenuItem,
        ]);
        $deleteMenuItem->disableIf(function () use ($request) {
            return 'Logs' === $request->getParam('controller');
        });
        $menu->addMenuItem($deleteMenuItem);

        $event->setResult($event);
    }
}
