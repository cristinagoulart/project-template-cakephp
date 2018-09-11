<?= $this->element('Menu.menu', [
    'name' => MENU_MAIN,
    'user' => $user,
    'renderer' => 'Menu\\MenuBuilder\\MainMenuRenderAdminLte'
]) ?>