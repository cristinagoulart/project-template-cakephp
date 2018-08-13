<?= $this->Form->create('User') ?>
<fieldset>
    <legend><?= __d('CakeDC/Users', 'Reset password') ?></legend>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-user"></span>
            </span>
            <?= $this->Form->input('reference', [
                'required' => true,
                'label' => false,
                'placeholder' => 'Username',
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
                ['class' => 'btn btn-primary btn-block btn-flat']
            ); ?>
        </div>
    </div>
</fieldset>
<?= $this->Form->end() ?>
