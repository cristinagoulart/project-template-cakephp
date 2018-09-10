<?= $this->element('Menu.menu', [
    'name' => \App\Menu\MenuName::MODULE_INDEX_ROW,
    'user' => $user,
    'renderer' => \Menu\MenuBuilder\MenuActionsRender::class,
    'context' => $entity
]) ?>

