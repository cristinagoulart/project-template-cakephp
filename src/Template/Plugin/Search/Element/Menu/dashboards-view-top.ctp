<?= $this->cell('Menu.Menu', [
    'name' => \App\Menu\MenuName::DASHBOARD_VIEW,
    'user' => $user,
    'fullBaseUrl' => false,
    'renderer' => \Menu\MenuBuilder\MenuButtonsRender::class,
    'content' => $entity,
]) ?>
