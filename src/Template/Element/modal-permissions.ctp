<?php
use Cake\ORM\TableRegistry;

$this->Html->script(
    [
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.switch-target',
        'RolesCapabilities.permissions',
    ],
    ['block' => 'scriptBottom']
);

$this->Html->css(
    [
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    ['block' => 'css']
);

// get all users
$table = TableRegistry::get('Users');
$users = $table->find('list')->where(['active' => true])->toArray();
$users[''] = '';
asort($users);

// get all groups
$table = TableRegistry::get('Groups.Groups');
$groups = $table->find('list')->toArray();
$groups[''] = '';
asort($groups);

// get existing permissions
$table = TableRegistry::get('RolesCapabilities.Permissions');
$query = $table->find()
    ->where(['model' => $this->name, 'foreign_key' => $id])
    ->limit(100);
$permissions = $query->all();
?>
<div class="modal fade" id="permissions-modal-add" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="mySetsLabel"><?= __('Add Permissions') ?></h4>
            </div>
            <div class="modal-body">
                <?= $this->Form->create('RolesCapabilities.Permissions', [
                    'url' => '/roles-capabilities/permissions/add',
                    'id' => 'modal-form-permissions-add'
                ]) ?>
                <div class="sets-feedback-container"></div>
                <?= $this->Form->hidden('foreign_key', ['value' => $id]) ?>
                <?= $this->Form->hidden('plugin', ['value' => $this->plugin]) ?>
                <?= $this->Form->hidden('model', ['value' => $this->name]) ?>
                <div class="row">
                    <div class="col-xs-6">
                        <?= $this->Form->control('type', [
                            'type' => 'select',
                            'options' => ['user' => 'User', 'group' => 'Group'],
                            'class' => 'select2',
                            'empty' => true,
                            'id' => 'permission-type'
                        ]) ?>
                    </div>
                    <div class="col-xs-6">
                        <div id="type-inner-container"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <?= $this->Form->label(__('Permission')) ?>
                        <?= $this->Form->select(
                            'type',
                            ['' => '', 'view' => 'View', 'edit' => 'Edit', 'delete' => 'Delete'],
                            ['class' => 'select2', 'multiple' => false, 'required' => true]
                        ) ?>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-xs-12">
                        <?= $this->Form->button(__('Submit'), [
                            'name' => 'btn_operation',
                            'value' => 'submit',
                            'class' => 'btn btn-primary pull-right'
                        ]) ?>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
                <br />
                <div id="type-outer-container" class="hidden">
                    <div id="permission-user">
                        <?= $this->Form->label(__('User')) ?>
                        <?= $this->Form->select('user_id', $users, [
                            'id' => 'permission-user',
                            'class' => 'select2',
                            'multiple' => false,
                            'required' => false
                        ]) ?>
                    </div>
                    <div id="permission-group">
                        <?= $this->Form->label(__('Groups')) ?>
                        <?= $this->Form->select('group_id', $groups, [
                            'id' => 'permission-group',
                            'class' => 'select2',
                            'multiple' => false,
                            'required' => false
                        ]) ?>
                    </div>
                </div>
                <table class="table table-hover table-condensed table-vertical-align">
                    <thead>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Model') ?></th>
                            <th><?= __('Permission') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($permissions as $permission) : ?>
                        <?php $table = TableRegistry::get($permission->get('owner_model')) ?>
                        <?php $displayField = $table->getDisplayField() ?>
                        <?php $primaryKey = $table->getPrimaryKey() ?>
                        <?php $entity = $table->get($permission->get('owner_foreign_key')) ?>
                        <tr>
                            <td><?= $entity->get($displayField) ?></td>
                            <td><?= $permission->get('owner_model') ?></td>
                            <td><?= $permission->get('type') ?></td>
                            <td>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    '/roles-capabilities/permissions/delete/' . $permission->get($primaryKey),
                                    [
                                        'class' => 'btn btn-default btn-xs',
                                        'confirm' => 'Are you sure you want to delete this permission?',
                                        'data' => [
                                            'plugin' => $this->plugin,
                                            'model' => $this->name,
                                            'foreign_key' => $id,
                                        ],
                                        'escape' => false,
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
