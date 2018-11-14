<?php
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use RolesCapabilities\Access\AccessFactory;

$fhf = new FieldHandlerFactory($this);

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min'
    ],
    ['block' => 'scriptBottom']
);

echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({
        stateSave:true,
        paging:true,
        searching:false
    });',
    ['block' => 'scriptBottom']
);

?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Users');?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->Html->link(
                        '<i class="fa fa-plus"></i> ' . __('Add'),
                        ['controller' => 'Users', 'action' => 'add'],
                        ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                <tr>
                    <th><?= __('Username') ?></th>
                    <th><?= __('Email') ?></th>
                    <th><?= __('First Name') ?></th>
                    <th><?= __('Last Name') ?></th>
                    <th><?= __('Gender') ?></th>
                    <th><?= __('Birthdate') ?></th>
                    <th><?= __('Active') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __d('Users', 'Actions') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($Users as $user) : ?>
                    <tr>
                        <td>
                            <?php if (Configure::read('Theme.prependAvatars', true) && !empty($user->image_src)): ?>
                            <img alt="Thumbnail" src="<?= $user->image_src ?>" style="width: 20px; height: 20px;" class="img-circle">
                            <?php endif; ?>
                            <?= h($user->username) ?>
                        </td>
                        <td><?= h($user->email) ?></td>
                        <td><?= h($user->first_name) ?></td>
                        <td><?= h($user->last_name) ?></td>
                        <td><?php
                            $definition = [
                                'name' => 'gender',
                                'type' => 'list(genders)',
                                'required' => false
                            ];
                            echo $fhf->renderValue('Users', 'gender', $user, ['fieldDefinitions' => $definition]);
                            ?></td>
                        <td><?= $user->has('birthdate') ? $user->birthdate->i18nFormat('yyyy-MM-dd') : '' ?></td>
                        <td><?= $user->active ? 'Yes' : 'No' ?></td>
                        <td><?= h($user->created->i18nFormat('yyyy-MM-dd hh:mm:ss')) ?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                                <?= $this->Html->link(
                                    '<i class="fa fa-eye"></i>',
                                    ['controller' => 'Users', 'action' => 'view', $user->id],
                                    ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['controller' => 'Users', 'action' => 'edit', $user->id],
                                    ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-lock"></i>',
                                    ['action' => 'change-user-password', $user->id],
                                    ['title' => __('Change User Password'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ) ?>
                                <?php
                                    $factory = new AccessFactory();
                                    if($factory->hasAccess(['controller' => 'settings', 'action' => 'user'], $user->id)): 
                                ?>
                                    <?= $this->Html->link(
                                        '<i class="fa fa-gears"></i>',
                                        ['controller' => 'settings', 'action' => 'user', $user->id],
                                        ['title' => __('Change User Settings'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                    ) ?>
                                <?php endif; ?>
                                <?php if (in_array($user->username, $lockedUsers)): ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fa fa-trash"></i>',
                                        [],
                                        [
                                            'title' => __('User can not be deleted'),
                                            'class' => 'btn btn-default btn-sm',
                                            'escape' => false,
                                            'disabled' => true,
                                            'onClick' => 'return false',
                                        ]
                                    ) ?>
                                <?php else: ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fa fa-trash"></i>',
                                        ['controller' => 'Users', 'action' => 'delete', $user->id],
                                        [
                                            'confirm' => __('Are you sure you want to delete user {0}?', $user->username),
                                            'title' => __('Delete'),
                                            'class' => 'btn btn-default btn-sm',
                                            'escape' => false,
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>