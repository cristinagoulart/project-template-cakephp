<?php
use Cake\Core\Configure;

echo $this->element('background-image-dark');
?>
<?= $this->Form->create($user); ?>
<fieldset>
    <?= $this->Form->input('username', ['placeholder' => 'Username', 'required' => true, 'label' => false]); ?>
    <?= $this->Form->input('email', ['placeholder' => 'Email', 'required' => true, 'label' => false]); ?>
    <?= $this->Form->input('password', ['placeholder' => 'Password', 'required' => true, 'label' => false]); ?>
    <?= $this->Form->input('password_confirm', ['type' => 'password', 'placeholder' => 'Confirm password', 'required' => true, 'label' => false]); ?>
    <?= $this->Form->input('first_name', ['placeholder' => 'First name', 'label' => false]); ?>
    <?= $this->Form->input('last_name', ['placeholder' => 'Last name', 'label' => false]); ?>
    <?php if (!Configure::read('Users.Tos.required')) : ?>
        <div class="form-group">
        <?php
            $label = $this->Form->label('tos', __d('Users', 'Accept TOS conditions?'));
            echo $this->Form->input('tos', [
                'type' => 'checkbox',
                'class' => 'square',
                'required' => true,
                'label' => false,
                'templates' => [
                    'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
                ]
            ]);
        ?>
        </div>
    <?php endif; ?>
    <?php
    if (Configure::read('Users.Registration.reCaptcha') && Configure::read('Users.reCaptcha.registration')) {
        echo $this->User->addReCaptcha();
    }
    ?>
</fieldset>
<?= $this->Form->button(__('Register'), ['class' => 'btn btn-primary btn-block']) ?>
<?= $this->Form->end() ?>
