<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility\User;
use Search\Model\Entity\SavedSearch;

$savedSearch = $widget->getData();
if (! $savedSearch instanceof SavedSearch) {
    return '';
}

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$table = TableRegistry::get($savedSearch->get('model'));
$groupBy = Hash::get($savedSearch->get('content'), 'saved.group_by', '');
$filters = $this->Search->getFilters($savedSearch->get('model'));

$headers = [];
if ('' !== $groupBy) {
    $key = array_search($groupBy, array_column($filters, 'field'));
    $headers[] = ['value' => $groupBy, 'text' => $filters[$key]['label']];
    $headers[] = ['value' => $savedSearch->get('model') . '.total', 'text' => 'Total'];
}

if ('' === $groupBy) {
    foreach (Hash::get($savedSearch->get('content'), 'saved.display_columns', []) as $item) {
        $key = array_search($item, array_column($filters, 'field'));
        $headers[] = ['value' => $filters[$key]['field'], 'text' => $filters[$key]['label']];
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
    ', ['block' => 'script']);

    echo $this->Html->script(
        [
            'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js',
            'Search./plugins/Chart.min.js',
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
                <a href="<?= '#' . $chart['id'] ?>" data-toggle="tab" aria-expanded="false">
                    <i class="fa fa-<?= $chart['icon'] ?>"></i>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="pull-left header"><?= $this->Html->link($savedSearch->get('name'), $url) ?></li>
    </ul>
    <div class="tab-content">
        <div id="table_<?= $uniqid ?>" class="tab-pane <?= empty($charts) ? 'active' : '' ?>">
            <table-ajax
                :data='<?= json_encode([
                    'criteria' => Hash::get($savedSearch->get('content'), 'saved.criteria', []),
                    'group_by' => $groupBy
                ]) ?>'
                :headers='<?= json_encode($headers) ?>'
                model="<?= Inflector::dasherize($savedSearch->get('model')) ?>"
                order-direction="<?= Hash::get($savedSearch->get('content'), 'saved.sort_by_order', '') ?>"
                order-field="<?= Hash::get($savedSearch->get('content'), 'saved.sort_by_field', '') ?>"
                primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
                request-type="POST"
                url="/api/<?= Inflector::dasherize($savedSearch->get('model')) ?>/search"
            ></table-ajax>
        </div>
        <?php foreach ($charts as $key => $chart) : ?>
            <div id="<?= $chart['id'] ?>" class="tab-pane <?= count($charts) === $key + 1 ? 'active' : '' ?>">
                <canvas id="canvas_<?= $chart['id'] ?>" ></canvas>
            </div>
        <?php endforeach; ?>
    </div>
</div>