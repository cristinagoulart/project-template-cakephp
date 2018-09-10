<?= $this->element('Menu.menu', [
    'name' => \App\Menu\MenuName::MODULE_VIEW,
    'user' => $user,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class,
    'context' => $options['entity'],
]) ?>