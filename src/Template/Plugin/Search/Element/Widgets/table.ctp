<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility\User;

$savedSearch = $widget->getData();
if (null === $savedSearch) {
    return '';
}

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$table = TableRegistry::get($savedSearch->get('model'));
$groupBy = Hash::get($savedSearch->get('content'), 'saved.group_by', '');

$headers = [];
if ('' !== $groupBy) {
    foreach ($this->Search->getFilters($savedSearch->get('model')) as $filter) {
        if ($filter['field'] === $groupBy) {
            $headers[] = ['value' => $groupBy, 'text' => $filter['label']];
            break;
        }
    }
    $headers[] = ['value' => $savedSearch->get('model') . '.total', 'text' => 'Total'];
}

if ('' === $groupBy) {
    foreach ($this->Search->getFilters($savedSearch->get('model')) as $filter) {
        if (in_array($filter['field'], Hash::get($savedSearch->get('content'), 'saved.display_columns', []))) {
            $headers[] = ['value' => $filter['field'], 'text' => $filter['label']];
        }
    }
}

list($plugin, $controller) = pluginSplit($savedSearch->get('model'));
$url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search', $savedSearch->get('id')];
$charts = '' !== $groupBy ? $this->Search->getChartOptions($savedSearch) : [];

if (! empty($charts)) {
    echo $this->Html->css('AdminLTE./bower_components/morris.js/morris', ['block' => 'css']);

    echo $this->Html->scriptBlock('
        var chartsData = chartsData || [];
        chartsData = chartsData.concat(' . json_encode($charts) . ');
    ', ['block' => 'scriptBottom']);

    echo $this->Html->script(
        [
            'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js',
            'AdminLTE./bower_components/morris.js/morris.min',
            'Qobo/Utils./plugins/d3/d3.min',
            'Qobo/Utils./plugins/d3/extensions/d3-funnel.min',
            'Search.reportGraphs'
        ],
        ['block' => 'scriptBottom']
    );
}
$uniqid = uniqid();
?>
<div class="dashboard-widget-saved-search nav-tabs-custom">
    <ul class="nav nav-tabs pull-right" id="widget-<?= md5(implode('', $url)) ?>">
        <li class="<?= empty($charts) ? 'active' : '' ?>">
            <a href="#table_<?= $uniqid ?>" data-toggle="tab" aria-expanded="true">
                <i class="fa fa-table"></i>
            </a>
        </li>
        <?php foreach ($charts as $key => $chart) : ?>
            <li class="<?= count($charts) === $key + 1 ? 'active' : '' ?>">
                <a href="<?= '#' . $chart['options']['element'] ?>" data-toggle="tab" aria-expanded="false">
                    <i class="fa fa-<?= $chart['icon'] ?>"></i>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="pull-left header"><?= $this->Html->link($savedSearch->get('name'), $url) ?></li>
    </ul>
    <div class="tab-content">
        <div id="table_<?= $uniqid ?>" class="tab-pane <?= empty($charts) ? 'active' : '' ?>">
            <table-ajax
                :batch="false"
                :data='<?= json_encode([
                    'criteria' => Hash::get($savedSearch->get('content'), 'saved.criteria', []),
                    'group_by' => $groupBy
                ]) ?>'
                :headers='<?= json_encode($headers) ?>'
                model="<?= Inflector::dasherize($savedSearch->get('model')) ?>"
                order-field="<?= Hash::get($savedSearch->get('content'), 'saved.sort_by_field', '') ?>"
                order-direction="<?= Hash::get($savedSearch->get('content'), 'saved.sort_by_order', '') ?>"
                primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
                request-type="POST"
                url="/api/<?= Inflector::dasherize($savedSearch->get('model')) ?>/search"
                :with-actions="<?= '' === $groupBy ? 'true' : 'false' ?>"
            ></table-ajax>
        </div>
        <?php foreach ($charts as $key => $chart) : ?>
            <div id="<?= $chart['options']['element'] ?>" class="tab-pane <?= count($charts) === $key + 1 ? 'active' : '' ?>"></div>
        <?php endforeach; ?>
    </div>
</div>