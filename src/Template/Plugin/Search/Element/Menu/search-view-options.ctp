<?php
deprecationWarning('"App.Search/Menu/search-view-options" element is deprecated.');

use Cake\Core\Configure;
use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();

$url = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'search'];
echo $this->Html->link('<i class="fa fa-undo"></i> ' . __('Reset'), $url, [
    'class' => 'btn btn-default', 'escape' => false
]) . '&nbsp;';

$url = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'exportSearch'];
if ($accessFactory->hasAccess($url, $user)) {
    $url[] = $id;
    $url[] = $name;
    echo $this->Html->link('<i class="fa fa-download"></i> ' . __('Export'), $url, [
        'class' => 'btn btn-default', 'escape' => false
    ]);
}

if (Configure::read('Search.batch.active')) : ?>
&nbsp;<div class="btn-group">
    <?= $this->Form->button('<i class="fa fa-bars"></i> Batch', [
        'id' => 'batch-button',
        'type' => 'button',
        'class' => 'btn btn-default dropdown-toggle',
        'data-toggle' => 'dropdown',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'disabled' => true
    ]) ?>
    <ul class="dropdown-menu">
        <li><?= $this->Html->link(
            '<i class="fa fa-pencil"></i> ' . __('Edit'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-edit-button',
                'data-batch' => true,
                'data-batch-url' => $this->Url->build([
                    'plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch', 'edit'
                ]),
                'escape' => false
            ]
        ) ?></li>
        <li><?= $this->Html->link(
            '<i class="fa fa-trash"></i> ' . __('Delete'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-delete-button',
                'data-batch' => true,
                'data-batch-url' => $this->Url->build([
                    'plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch', 'delete'
                ]),
                'data-batch-confirm' => 'Are you sure you want to delete the selected records?',
                'escape' => false
            ]
        ) ?></li>
    </ul>
</div>
<?php endif; ?>