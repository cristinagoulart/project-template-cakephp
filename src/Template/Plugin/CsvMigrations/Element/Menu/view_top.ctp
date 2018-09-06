<?= $this->cell('Menu.Menu', [
    'name' => \App\Menu\MenuName::MODULE_VIEW,
    'user' => $user,
    'fullBaseUrl' => false,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class,
    'content' => $options['entity'],
]) ?>