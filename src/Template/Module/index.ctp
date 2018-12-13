<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
$title = isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name));

$displayFields = Hash::get($searchData, 'display_columns', []);
$scripts = [];
foreach ($searchableFields as $field => $options) {
    if (! in_array($field, $displayFields)) {
        continue;
    }

    if (empty($options['input']['post'])) {
        continue;
    }
    array_push($scripts, ['post' => $options['input']['post']]);
}

echo $this->element('Search.widget_libraries', ['scripts' => $scripts]);
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
<?php
$args = [
    [
        'entity' => $entity,
        'searchData' => $searchData,
        'searchableFields' => $searchableFields,
        'associationLabels' => $associationLabels,
        'batch' => (bool)Configure::read('Search.batch.active'),
        'preSaveId' => $preSaveId,
        'action' => 'index'
    ],
    $this
];

$cell = $this->cell('Search.Result', $args);

echo $cell;
?>
</section>
