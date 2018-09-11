<?= $this->element('Menu.menu', [
    'name' => \App\Menu\MenuName::DASHBOARD_VIEW,
    'user' => $user,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class,
    'context' => $entity,
]) ?>
