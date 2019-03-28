<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility\User;

$savedSearch = $widget->getData();
$widgetOptions = $widget->getOptions();

if (null === $savedSearch) {
    return '';
}

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$content = $savedSearch->get('content')['saved'];

$headers = [];
if (! empty($content['group_by'])) {
    foreach ($this->Search->getFilters($savedSearch->get('model')) as $filter) {
        if ($filter['field'] === $content['group_by']) {
            $headers[] = ['value' => $content['group_by'], 'text' => $filter['label']];
            break;
        }
    }
    $headers[] = ['value' => $savedSearch->get('model') . '.total', 'text' => 'Total'];
}

if (empty($content['group_by'])) {
    foreach ($this->Search->getFilters($savedSearch->get('model')) as $filter) {
        if (in_array($filter['field'], $content['display_columns'])) {
            $headers[] = ['value' => $filter['field'], 'text' => $filter['label']];
        }
    }
}

$data = [
    'criteria' => empty($content['criteria']) ? [] : $content['criteria'],
    'group_by' => empty($content['group_by']) ? '' : $content['group_by']
];

list($plugin, $controller) = pluginSplit($savedSearch->get('model'));
$url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search', $savedSearch->get('id')];
$charts = ! empty($content['group_by']) ? $this->Search->getChartOptions($savedSearch) : [];

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
        <li class="pull-left header"><?= $this->Html->link($savedSearch->has('name') ? $savedSearch->get('name') : $this->name, $url) ?></li>
    </ul>
    <div class="tab-content">
        <div id="table_<?= $uniqid ?>" class="tab-pane <?= empty($charts) ? 'active' : '' ?>">
            <table-ajax
                :batch="false"
                :data='<?= json_encode($data) ?>'
                :headers='<?= json_encode($headers) ?>'
                model="<?= Inflector::dasherize($savedSearch->get('model')) ?>"
                order-field="<?= $content['sort_by_field'] ?>"
                order-direction="<?= $content['sort_by_order'] ?>"
                primary-key="<?= TableRegistry::get($savedSearch->get('model'))->getPrimaryKey() ?>"
                request-type="POST"
                url="/api/<?= Inflector::dasherize($savedSearch->get('model')) ?>/search"
                :with-actions="<?= empty($content['group_by']) ? 'true' : 'false' ?>"
            ></table-ajax>
        </div>
        <?php foreach ($charts as $key => $chart) : ?>
            <div id="<?= $chart['options']['element'] ?>" class="tab-pane <?= count($charts) === $key + 1 ? 'active' : '' ?>"></div>
        <?php endforeach; ?>
    </div>
</div>