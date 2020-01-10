<?php
use Cake\Core\Configure;

$this->layout = 'AdminLTE/login';
?>
<?= $this->Form->create() ?>
<fieldset>
    <legend><?= __('Sign in') ?></legend>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-user"></span>
            </span>
            <?= $this->Form->control('username', [
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
            <?= $this->Form->control('password', [
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
        <?php if (Configure::read('Users.RememberMe.active')) : ?>
        <div class="col-xs-12">
            <div class="checkbox icheck">
                <?= $this->Form->control(Configure::read('Users.Key.Data.rememberMe'), [
                    'type' => 'checkbox',
                    'label' => ' ' . __d('CakeDC/Users', 'Remember Me'),
                    'templates' => ['inputContainer' => '{{content}}']
                ]); ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-12">
            <?= $this->Form->button(
                '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> ' . __('Sign in'),
                ['class' => 'btn btn-primary btn-block']
            ); ?>
        </div>
    </div>
</fieldset>
<?= implode(' ', $this->User->socialLoginList()); ?>
<?= $this->Form->end() ?>
<?php
if (!(bool)Configure::read('Ldap.enabled')) {
    echo $this->Html->link(__d('CakeDC/Users', 'Reset Password'), ['action' => 'requestResetPassword']);
}

if ((bool)Configure::read('Users.Registration.active') && (bool)Configure::read('Users.Email.validate')) {
    echo $this->Html->link(__d('CakeDC/Users', 'Resend validation email'), [
        'controller' => 'Users',
        'action' => 'resendTokenValidation'
        ], ['class' => 'pull-right']
    );
}

if ((bool)Configure::read('Users.Registration.active')) {
    echo '<hr />';
    echo $this->Html->link(__d('CakeDC/Users', 'Register'), ['action' => 'register']);
}
