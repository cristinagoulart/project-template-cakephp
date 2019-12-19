<?= $this->element('Menu.menu', [
    'name' => \App\Menu\MenuName::MODULE_INDEX_TOP,
    'user' => $user,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class
]) ?>
