<?php
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Search\Aggregate\AggregateInterface;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$table = TableRegistry::get($savedSearch->get('model'));
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

$config = (new ModuleConfig(ConfigType::MODULE(), $controller))->parse();
$title = isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($controller));
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $title ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <?= $this->element('Module/Menu/index_top', ['user' => $user]) ?>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table-ajax
                :data='<?= json_encode([
                    'criteria' => $savedSearch->get('criteria'),
                    'group_by' => (string)$savedSearch->get('group_by')
                ]) ?>'
                :headers='<?= json_encode($headers) ?>'
                model="<?= Inflector::dasherize($savedSearch->get('model')) ?>"
                order-direction="<?= (string)$savedSearch->get('order_by_direction') ?>"
                order-field="<?= (string)$savedSearch->get('order_by_field') ?>"
                primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
                request-type="POST"
                url="/api/<?= Inflector::dasherize($savedSearch->get('model')) ?>/search"
                :with-actions="<?= ! $disableBatch ? 'true' : 'false' ?>"
                :with-batch-delete="<?= ! $disableBatch && $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
                :with-batch-edit="<?= ! $disableBatch && $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
            ></table-ajax>
        </div>
    </div>
</section>
