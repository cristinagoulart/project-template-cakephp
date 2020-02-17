<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Search\Aggregate\AggregateInterface;
use Search\Model\Entity\SavedSearch;

$savedSearch = $widget->getData();
if (! $savedSearch instanceof SavedSearch) {
    return '';
}

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$table = TableRegistry::getTableLocator()->get($savedSearch->get('model'));
$filters = $this->Search->getFilters($savedSearch->get('model'));

$hasAggregate = false;
$headers = [];
foreach ((array)$savedSearch->get('fields') as $item) {
    if (1 === preg_match(AggregateInterface::AGGREGATE_PATTERN, $item)) {
        $hasAggregate = true;
        preg_match(AggregateInterface::AGGREGATE_PATTERN, $item, $matches);
        list(, $aggregateField) = pluginSplit($matches[2]);
        $key = array_search($matches[2], array_column($filters, 'field'), true);
        $label = sprintf('%s (%s)', $filters[$key]['label'], $matches[1]);
    } else {
        $key = array_search($item, array_column($filters, 'field'), true);
        $label = false !== $key ? $filters[$key]['label'] : $item;
    }
    $headers[] = ['value' => $item, 'text' => $label];
}
$disableBatch = '' !== (string)$savedSearch->get('group_by') || $hasAggregate;

$accessFactory = new AccessFactory();
list($plugin, $controller) = pluginSplit($savedSearch->get('model'));
$urlBatch = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'batch'];
$urlExport = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'exportSearch'];

$charts = $this->Search->getChartOptions($savedSearch);
if ([] !== $charts) {
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

$url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'search', $savedSearch->get('id')];
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
                :data='<?= h(json_encode([
                    'criteria' => $savedSearch->get('criteria'),
                    'group_by' => (string)$savedSearch->get('group_by')
                ])) ?>'
                :headers='<?= h(json_encode($headers)) ?>'
                model="<?= Inflector::dasherize($savedSearch->get('model')) ?>"
                order-direction="<?= (string)$savedSearch->get('order_by_direction') ?>"
                order-field="<?= (string)$savedSearch->get('order_by_field') ?>"
                primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
                request-type="POST"
                url="/api/<?= Inflector::dasherize($savedSearch->get('model')) ?>/search"
                :with-actions="<?= ! $disableBatch ? 'true' : 'false' ?>"
                :with-batch-delete="<?= ! $disableBatch && $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
                :with-batch-edit="<?= ! $disableBatch && $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
                :with-export="<?= ! $disableBatch && $accessFactory->hasAccess($urlExport, $user) ? 'true' : 'false' ?>"
            ></table-ajax>
        </div>
        <?php foreach ($charts as $key => $chart) : ?>
            <div id="<?= $chart['id'] ?>" class="tab-pane <?= count($charts) === $key + 1 ? 'active' : '' ?>">
                <canvas id="canvas_<?= $chart['id'] ?>" ></canvas>
            </div>
        <?php endforeach; ?>
    </div>
</div>
