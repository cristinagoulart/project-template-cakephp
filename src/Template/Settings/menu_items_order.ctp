<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Cake\ORM\TableRegistry;

$fhf = new FieldHandlerFactory($this);

$currentDashboardOrder = $configure['dashboard_menu_order_value'];
$currentDashboardOrderJson = json_decode($currentDashboardOrder, true) ?? [];

if ($dashboards) {
    foreach($currentDashboardOrderJson as $id => $order) {
        if (empty($dashboards[$id])) {
            continue;
        }

        //move element to buttom
        $value = $dashboards[$id];
        unset($dashboards[$id]);
        $dashboards[$id] = $value;
    }
}

echo $this->Html->script('AdminLTE./bower_components/jquery-ui/jquery-ui.min', ['block' => 'script']);

?>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    (function ($) {

        $("ul.dashboard-menu-items").sortable({
            containment: 'parent',
            update: function (event, ui) {
                var items = {}
                $("li.dashboard-menu-item").each(function(){
                    items [$(this).attr('id')] = $(this).index();
                })

                $('#settings-dashboard_menu_order_value').val(JSON.stringify(items));
            }
        });

    })(jQuery);
<?= $this->Html->scriptEnd() ?>

<section class="content-header">
	<h1><?= __('Settings'); ?>
		<?= isset($afterTitle) ? ' » '. $afterTitle : '' ?>
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-6">
			<?= $this->Form->create($settings); ?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><?= $this->Form->label(__('Order Dashboard Items')); ?></h3>
                </div>
                <div class="box-body">
                    <ul class="dashboard-menu-items" style="display: block; list-style: none;">
                    <?php foreach($dashboards as $dashboatdId => $dashboard) : ?>
                        <li style="height:50px;" class="dashboard-menu-item" id="<?=$dashboatdId?>"><a type="button" title="<?=$dashboard?>" class="btn btn-default btn-block"><?=$dashboard?></a></li>
                    <?php endforeach; ?>
                </div>
            </div>

			<?php
                echo $this->Form->hidden('Settings[dashboard_menu_order_value]', ['id' => 'settings-dashboard_menu_order_value', 'value' => $currentDashboardOrder]);
				echo $this->Form->button(__('Submit'), ['class' => 'btn btn-primary','value' => 'submit']);
				echo $this->Form->end();
			?>
		</div>
	</div>
</section>
