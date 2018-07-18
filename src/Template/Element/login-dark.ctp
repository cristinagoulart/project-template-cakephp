<?php
use Cake\Core\Configure;
?>
<?= $this->Form->create() ?>
<fieldset>
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
        <?php if (Configure::read('Users.RememberMe.active')) : ?>
        <div class="col-xs-12">
            <div class="checkbox icheck">
                <?= $this->Form->input(Configure::read('Users.Key.Data.rememberMe'), [
                    'type' => 'checkbox',
                    'label' => ' ' . __d('Users', 'Remember Me'),
                    'templates' => ['inputContainer' => '{{content}}']
                ]); ?>
            </div>
        </div>
        <?php endif; ?>
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
if (Configure::read('Users.Email.required') && !(bool)Configure::read('Ldap.enabled')) {
    echo $this->Html->link(__d('users', 'I forgot my password'), ['action' => 'requestResetPassword']) . '<br />';
}
if (Configure::read('Users.Registration.active')) {
    echo $this->Html->link(__d('users', 'Register a new membership'), ['action' => 'register']);
}
?>
