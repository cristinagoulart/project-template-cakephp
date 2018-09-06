<?php
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

trigger_error('"CsvMigrations.Menu/related_actions" element is deprecated.', E_USER_DEPRECATED);

$factory = new FieldHandlerFactory();

$primaryKey = $table->getPrimaryKey();
$displayField = $table->getDisplayField();
list($plugin, $controller) = pluginSplit($association->className());

$menu = [];

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $entity->get($primaryKey)];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-eye"></i>', $url, [
        'title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('View'),
    'icon' => 'eye',
    'type' => 'link_button',
    'order' => 10
];

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $entity->get($primaryKey)];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
        'title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('Edit'),
    'icon' => 'pencil',
    'type' => 'link_button',
    'order' => 20
];

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $entity->get($primaryKey)];
$confirm = __(
    'Are you sure you want to delete {0}?',
    $factory->renderValue($table, $displayField, $entity->get($displayField), ['renderAs' => 'plain'])
);
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'confirm' => $confirm,
        'title' => __('Delete'),
        'class' => 'btn btn-default btn-sm',
        'escape' => false
    ]),
    'url' => $url,
    'label' => __('Delete'),
    'icon' => 'trash',
    'type' => 'postlink_button',
    'order' => 40,
    'confirmMsg' => $confirm
];

if (in_array($association->type(), ['manyToMany'])) {
    $url = [
        'prefix' => false,
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'unlink',
        $options['id'],
        $association->getName(),
        $entity->get($primaryKey)
    ];
    $menu[] = [
        'html' => $this->Form->postLink('<i class="fa fa-chain-broken"></i>', $url, [
            'title' => __('Unlink'), 'class' => 'btn btn-default btn-sm', 'escape' => false
        ]),
        'url' => $url,
        'label' => __('Unlink'),
        'icon' => 'unlink',
        'type' => 'postlink_button',
        'order' => 30
    ];
}

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'actions']);
