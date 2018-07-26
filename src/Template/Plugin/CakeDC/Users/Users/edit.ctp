<?php
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

echo $this->Html->css(
    [
        'AdminLTE./plugins/iCheck/all',
        'AdminLTE./plugins/datepicker/datepicker3',
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'AdminLTE./plugins/iCheck/icheck.min',
        'AdminLTE./plugins/datepicker/bootstrap-datepicker',
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2.init'
    ],
    [
        'block' => 'scriptBottom'
    ]
);
echo $this->Html->scriptBlock(
    '$(\'input[type="checkbox"].square, input[type="radio"].square\').iCheck({
        checkboxClass: "icheckbox_square",
        radioClass: "iradio_square"
    });',
    ['block' => 'scriptBottom']
);

echo $this->Html->scriptBlock(
    'csv_migrations_select2.setup(' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.select2'),
            Configure::read('API')
        )
    ) . ');',
    ['block' => 'scriptBottom']
);
?>
<section class="content-header">
    <h1><?= __('Edit {0}', ['User']) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="box box-primary">
                <div class="box-body">
                    <?php
                    $userImage = $Users->get('image_src');
                    if ($userImage) {
                        echo '<img src="' . $userImage . '" class="profile-user-img img-responsive img-circle" />';
                    } else {
                        echo $this->Html->image('user-image-160x160.png', [
                            'class' => 'profile-user-img img-responsive img-circle',
                            'alt' => 'User profile picture'
                        ]);
                    }
                    ?>
                    <h3 class="profile-username text-center"><?= $Users->username; ?></h3>
                    <?= $this->Form->create($Users, [
                        'type' => 'file',
                        'url' => ['plugin' => null, 'controller' => 'Users', 'action' => 'upload-image', $Users->id]
                    ]) ?>
                    <div class="form-group">
                        <?= $this->Form->label($userImage ? __('Replace image') : __('Upload image')) ?>
                        <?= $this->Form->file('Users.image') ?>
                    </div>
                </div>
                <div class="box-footer">
                    <?= $this->Form->button(__('Upload'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?= $this->Form->end() ?>
                <!-- /.box-body -->
            </div>
        </div>
        <div class="col-md-9">
            <?= $this->Form->create($Users) ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('User Information') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('username'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('active', [
                                'type' => 'checkbox',
                                'class' => 'square',
                                'label' => false,
                                'templates' => [
                                    'inputContainer' => '<div class="{{required}}">' . $this->Form->label('Users.active') . '<div class="clearfix"></div>{{content}}</div>'
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Personal Details') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('first_name'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('last_name'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?php
                                $definition = [
                                    'name' => 'country',
                                    'type' => 'list(countries)',
                                    'required' => false,
                                ];

                                $inputField = $fhf->renderInput('Users', 'country', null, ['fieldDefinitions' => $definition]);
                                echo $inputField;
                            ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('initials'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?php
                                $definition = [
                                    'name' => 'gender',
                                    'type' => 'list(genders)',
                                    'required' => false,
                                ];

                                echo $fhf->renderInput('Users', 'gender', null, ['fieldDefinitions' => $definition]);
                            ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('birthdate', [
                                'type' => 'text',
                                'label' => 'Birthdate',
                                'data-provide' => 'datepicker',
                                'autocomplete' => 'off',
                                'data-date-format' => 'yyyy-mm-dd',
                                'data-date-autoclose' => true,
                                'value' => $Users->has('birthdate') ? $Users->birthdate->i18nFormat('yyyy-MM-dd') : null,
                                'templates' => [
                                    'input' => '<div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="{{type}}" name="{{name}}"{{attrs}}/>
                                    </div>'
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Contact Details') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('email'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('phone_office'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('phone_home'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('phone_mobile'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('phone_extension'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Company Details') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('company'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('department'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('team'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('position'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $fhf->renderInput('Users', 'reports_to', $Users, [
                                'fieldDefinitions' => [
                                    'name' => 'reports_to',
                                    'type' => 'related(Users)',
                                    'required' => false,
                                ]
                            ]); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('is_supervisor', [
                                'type' => 'checkbox',
                                'class' => 'square',
                                'label' => false,
                                'templates' => [
                                    'inputContainer' => '<div class="{{required}}">' . $this->Form->label('Users.is_supervisor') . '<div class="clearfix"></div>{{content}}</div>'
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                    <?php if (Configure::read('Users.GoogleAuthenticator.login')) : ?>
                    <?= $this->Form->postLink(
                        __d('CakeDC/Users', 'Reset Google Authenticator Token'),
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'resetGoogleAuthenticator',
                            $Users->id
                        ],
                        [
                            'class' => 'btn btn-danger',
                            'confirm' => __d(
                                'CakeDC/Users',
                                'Are you sure you want to reset token for user "{0}"?',
                                $Users->username
                            )
                        ]
                    ); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
