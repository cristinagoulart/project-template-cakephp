<?php use Cake\Core\Configure; ?>
<?= $this->Form->create() ?>
<fieldset>
    <legend><?= __d('CakeDC/Users', 'Login') ?></legend>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-user"></span>
            </span>
            <?= $this->Form->input('username', [
                'required' => true,
                'label' => false,
                'placeholder' => 'Username',
                'autofocus' => true,
                'templates' => [
                    'inputContainer' => '{{content}}'
                ]
            ]) ?>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-lock"></span>
            </span>
            <?= $this->Form->input('password', [
                'required' => true,
                'label' => false,
                'placeholder' => 'Password',
                'templates' => [
                    'inputContainer' => '{{content}}'
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <div class="checkbox icheck">
                <?php
                if (Configure::read('Users.RememberMe.active')) {
                    echo $this->Form->input(Configure::read('Users.Key.Data.rememberMe'), [
                        'type' => 'checkbox',
                        'label' => ' ' . __d('Users', 'Remember Me'),
                        'templates' => [
                            'inputContainer' => '{{content}}'
                        ]
                    ]);
                }
                ?>
            </div>
        </div>
        <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
            <?= $this->Form->button(
                '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> ' . __d('Users', 'Sign In'),
                ['class' => 'btn btn-primary btn-block']
            ); ?>
        </div>
    </div>
</fieldset>
<?= implode(' ', $this->User->socialLoginList()); ?>
<?= $this->Form->end() ?>
<?php
if (!(bool)Configure::read('Ldap.enabled')) {
    echo $this->Html->link(__d('users', 'I forgot my password'), ['action' => 'requestResetPassword']);
}

if ((bool)Configure::read('Users.Email.validate')) {
    echo $this->Html->link(__d('users', 'Resend Activation Email'), [
        'controller' => 'Users',
        'action' => 'resendTokenValidation'
        ], ['class' => 'pull-right']
    );
}

if ((bool)Configure::read('Users.Registration.active')) {
    echo '<hr />';
    echo $this->Html->link(__d('users', 'Register a new membership'), ['action' => 'register']);
}
?>
