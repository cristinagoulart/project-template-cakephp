<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b>:
		<?= $this->SystemInfo->getProjectVersion() ?>
    </div>
    <strong>
		Copyright &copy; <?php echo date('Y'); ?>,
		<?= $this->SystemInfo->getProjectName() ?>.
	</strong>
	All rights reserved.
</footer>
<?php
// @todo find a way to load this as part of 'block' => 'css'
echo $this->Html->css('custom');
$this->Html->script('Qobo/Utils.QoboStorage.js', ['block' => 'script']);
$this->Html->script('general.js', ['block' => 'scriptBottom']);
