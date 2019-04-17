<?php
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
$title = isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name));
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
    </div>
</section>