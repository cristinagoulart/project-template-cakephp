<?php
use App\Menu\MenuName;
use Cake\Event\Event;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;

$event = new Event((string)EventName::GET_MENU_ITEMS(), $entity, [
    'name' => MenuName::SEARCH_VIEW,
    'user' => $user,
]);
$this->eventManager()->dispatch($event);

/** @var \Menu\MenuBuilder\Menu $menu */
$menu = $event->getResult();
if (!($menu instanceof MenuInterface)) {
    return;
}

$menu->addMenuItem(MenuItemFactory::createMenuItem([
    'url' => [
        'prefix' => false,
        'plugin' => $plugin,
        'controller' => $associationController,
        'action' => 'unlink',
        $associationId,
        $associationName,
        $entity->id,
    ],
    'icon' => 'chain-broken',
    'label' => __('Unlink'),
    'dataType' => 'ajax-delete-record',
    'type' => 'postlink_button',
    'confirmMsg' => __(
        'Are you sure you want to unlink {0}?',
        empty(trim($entity->get($displayField))) ? 'this record' : strip_tags($entity->get($displayField))
    ),
    'order' => 200,
]));

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'actions']);
