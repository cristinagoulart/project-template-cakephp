<?= $this->cell('Menu.Menu', [
    'name' => \App\Menu\MenuName::MODULE_INDEX_ROW,
    'user' => $user,
    'fullBaseUrl' => false,
    'renderer' => \Menu\MenuBuilder\MenuActionsRender::class,
    'context' => $entity
]) ?>
