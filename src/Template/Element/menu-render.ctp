<?php
use Menu\MenuBuilder\Menu;

if (!isset($menuType)) {
    $menuType = Menu::MENU_BUTTONS_TYPE;
}

$renderClass = 'Menu\\MenuBuilder\\Menu' . ucfirst($menuType) . 'Render';
if (!class_exists($renderClass)) {
    throw new Exception('Menu render class [' . $renderClass . '] is not found!');
}

$render = new $renderClass($menu, $this);
echo $render->render();