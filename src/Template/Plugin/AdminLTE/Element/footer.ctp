<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b><?= __('Version') ?></b>:
		<?= $this->SystemInfo->getProjectVersion() ?>
    </div>
    <strong>
		<?= __('Copyright &copy; {0}, {1}', date('Y'), $this->SystemInfo->getProjectName()) ?>
	</strong>
	<?= __('All rights reserved.'); ?>
</footer>
<?php
// @todo find a way to load this as part of 'block' => 'css'
echo $this->Html->css('custom');
$this->Html->script('Qobo/Utils.QoboStorage.js', ['block' => 'script']);
$this->Html->script('general.js', ['block' => 'scriptBottom']);
