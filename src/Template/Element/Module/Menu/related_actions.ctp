<?php
$menu = [];

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $entity->id];
$menu[] = ['url' => $url, 'icon' => 'eye', 'label' => __('View'), 'type' => 'link_button', 'order' => 10];

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $entity->id];
$menu[] = ['url' => $url, 'icon' => 'pencil', 'label' => __('Edit'), 'type' => 'link_button', 'order' => 20];

$url = [
    'prefix' => 'api',
    'plugin' => $plugin,
    'controller' => $controller,
    'action' => 'delete',
    '_ext' => 'json',
    $entity->id
];
$menu[] = [
    'url' => $url,
    'icon' => 'trash',
    'label' => __('Delete'),
    'dataType' => 'ajax-delete-record',
    'type' => 'link_button',
    'confirmMsg' => __(
        'Are you sure you want to delete {0}?',
        $entity->has($displayField) && !empty($entity->{$displayField}) ?
                strip_tags($entity->{$displayField}) :
                'this record'
    ),
    'order' => 30
];

$url = [
    'prefix' => false,
    'plugin' => $plugin,
    'controller' => $associationController,
    'action' => 'unlink',
    $associationId,
    $associationName,
    $entity->id,
];

$menu[] = [
    'url' => $url,
    'icon' => 'chain-broken',
    'label' => __('Unlink'),
    'dataType' => 'ajax-delete-record',
    'type' => 'postlink_button',
    'confirmMsg' => __(
        'Are you sure you want to unlink {0}?',
        strip_tags($entity->{$displayField})
    ),
    'order' => 40
];

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'actions']);
