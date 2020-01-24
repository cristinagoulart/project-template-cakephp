<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Cake\ORM\TableRegistry;

$fhf = new FieldHandlerFactory($this);

// get all user dashboards
$table = TableRegistry::get('Search.Dashboards');
$dashboards = $table->find('list')->toArray();

$currentDashboardOrder = $configure['dashboard_menu_order_value'];
$currentDashboardOrderJson = json_decode($currentDashboardOrder) ?? [];

foreach($currentDashboardOrderJson as $currentDashboardOrderJsonItem) {
    if (empty($dashboards[$currentDashboardOrderJsonItem->id])) {
        continue;
    }

    //move element to bottom
    $value = $dashboards[$currentDashboardOrderJsonItem->id];
    unset($dashboards[$currentDashboardOrderJsonItem->id]);
    $dashboards[$currentDashboardOrderJsonItem->id] = $value;
}

echo $this->Html->script('AdminLTE./bower_components/jquery-ui/jquery-ui.min', ['block' => 'script']);

?>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    (function ($) {

        $("ul.dashboard-menu-items").sortable({
            containment: 'parent',
            update: function (event, ui) {
                var jsonObj = [];
                $("li.dashboard-menu-item").each(function(){
                    item = {}
                    item ["id"] = $(this).attr('id');
                    item ["order"] = $(this).index();

                    jsonObj.push(item);
                })
                $('#settings-dashboard_menu_order_value').val(JSON.stringify(jsonObj));
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
