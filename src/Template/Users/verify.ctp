<?php
$this->layout = 'AdminLTE/login';
echo $this->Form->create();
?>
<fieldset>
    <?php if (!empty($secretDataUri)):?>
        <p class='text-center'><img src="<?php echo $secretDataUri;?>"/></p>
    <?php endif;?>
    <div class="form-group has-feedback">
        <?= $this->Form->input('code', ['required' => true, 'label' => __d('CakeDC/Users', 'Verification Code')]) ?>
    </div>
    <?= $this->Form->button(__d('CakeDC/Users', '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Verify'), ['class' => 'btn btn-primary']); ?>
</fieldset>
<?= $this->Form->end() ?>
