<?php
use Cake\Core\Configure;

if (! $validatePassword) {
    $this->layout = 'AdminLTE/login';

    $element = 'change-password-' . (string)Configure::read('Theme.version');
    if (! $this->elementExists($element)) {
        $element = 'change-password-light';
    }

    echo $this->element($element);
}

if ($validatePassword) : ?>
<section class="content-header">
    <h1>Change Password</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <?= $this->Form->create(); ?>
                <div class="box-body">
                    <?= $this->Form->input('Users.current_password', [
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => __('Current Password')
                    ]); ?>
                    <?= $this->Form->input('Users.password', [
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => __('Password')
                    ]); ?>
                    <?= $this->Form->input('Users.password_confirm', [
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => __('Password Confirm')
                    ]); ?>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
