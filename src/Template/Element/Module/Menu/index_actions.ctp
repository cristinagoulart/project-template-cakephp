<?php

use Cake\Event\Event;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuInterface;

$event = new Event((string)EventName::GET_MENU_ITEMS(), $entity, [
    'name' => 'search_view',
    'user' => $user,
]);
$this->eventManager()->dispatch($event);

/** @var \Menu\MenuBuilder\Menu $menu */
$menu = $event->getResult();
if (!($menu instanceof MenuInterface)) {
    return;
}

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'actions']);
