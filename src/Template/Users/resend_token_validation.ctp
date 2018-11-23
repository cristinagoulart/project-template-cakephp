<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
$this->layout = 'AdminLTE/login';
?>
<?= $this->Form->create($user); ?>
<fieldset>
    <legend><?= __d('CakeDC/Users', 'Resend Validation Email') ?></legend>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-user"></span>
            </span>
            <?= $this->Form->control('reference', [
                'required' => true,
                'label' => false,
                'placeholder' => 'Email',
                'templates' => [
                    'inputContainer' => '{{content}}'
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
            <?= $this->Form->button(
                '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> ' . __d('Users', 'Submit'),
                ['class' => 'btn btn-primary btn-block']
            ); ?>
        </div>
    </div>
</fieldset>
<?= $this->Form->end() ?>
