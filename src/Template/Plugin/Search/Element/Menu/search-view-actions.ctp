<?= $this->element('Menu.menu', [
    'name' => \App\Menu\MenuName::SEARCH_VIEW,
    'user' => $user,
    'renderer' => \Menu\MenuBuilder\MenuActionsRender::class,
    'context' => $entity,
]) ?>