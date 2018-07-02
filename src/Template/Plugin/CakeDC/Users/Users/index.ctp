<?php
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);
?>
<section class="content-header">
    <h1>Users
        <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
            <?= $this->Html->link(
                '<i class="fa fa-plus"></i> ' . __('Add'),
                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'add'],
                ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
            ); ?>
            </div>
        </div>
    </h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body table-responsive">
            <table class="table table-hover table-condensed table-vertical-align">
                <thead>
                    <tr>
                <th><?= $this->Paginator->sort('username') ?></th>
                <th><?= $this->Paginator->sort('email') ?></th>
                <th><?= $this->Paginator->sort('first_name') ?></th>
                <th><?= $this->Paginator->sort('last_name') ?></th>
                <th><?= $this->Paginator->sort('gender') ?></th>
                <th><?= $this->Paginator->sort('birthdate') ?></th>
                <th><?= $this->Paginator->sort('is_supervisor') ?></th>
                <th><?= $this->Paginator->sort('is_superuser') ?></th>
                <th><?= $this->Paginator->sort('active') ?></th>
                <th class="actions"><?= __d('Users', 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Users as $user) : ?>
                    <tr>
                        <td><?= h($user->username) ?></td>
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
                        <td><?= $user->is_supervisor ? 'Yes' : 'No' ?></td>
                        <td><?= $user->is_superuser ? 'Yes' : 'No' ?></td>
                        <td><?= $user->active ? 'Yes' : 'No' ?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view', $user->id],
                                ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?= $this->Html->link(
                                '<i class="fa fa-pencil"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'edit', $user->id],
                                ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?= $this->Html->link(
                                '<i class="fa fa-lock"></i>',
                                ['action' => 'change-user-password', $user->id],
                                ['title' => __('Change User Password'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ) ?>
                            <?= $this->Form->postLink(
                                '<i class="fa fa-trash"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'delete', $user->id],
                                [
                                    'confirm' => __('Are you sure you want to delete # {0}?', $user->id),
                                    'title' => __('Delete'),
                                    'class' => 'btn btn-default btn-sm',
                                    'escape' => false
                                ]
                            ) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <div class="paginator">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?= $this->Paginator->prev('&laquo;', ['escape' => false]) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('&raquo;', ['escape' => false]) ?>
                </ul>
            </div>
        </div>
    </div>
</section>
