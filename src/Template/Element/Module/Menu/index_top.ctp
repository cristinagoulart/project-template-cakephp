<?php

use App\Menu\MenuName;
use Cake\Event\Event;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuInterface;

$event = new Event((string)EventName::GET_MENU_ITEMS(), null, [
    'name' => MenuName::MODULE_INDEX_TOP,
    'user' => $user,
]);
$this->eventManager()->dispatch($event);

/** @var \Menu\MenuBuilder\Menu $menu */
$menu = $event->getResult();
if (!($menu instanceof MenuInterface)) {
    return;
}

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'buttons']);
