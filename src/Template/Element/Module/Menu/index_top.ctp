<?= $this->cell('Menu.Menu', [
    'name' => \App\Menu\MenuName::MODULE_INDEX_TOP,
    'user' => $user,
    'fullBaseUrl' => false,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class
]) ?>