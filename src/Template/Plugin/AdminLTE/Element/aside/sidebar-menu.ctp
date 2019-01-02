<?php
use App\Menu\MenuName;

echo $this->element('Menu.menu', [
    'name' => MenuName::MAIN,
    'user' => $user,
    'renderer' => 'Menu\\MenuBuilder\\MainMenuRenderAdminLte'
]);
