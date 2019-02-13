<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility\User;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
$title = isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name));
$tableName = $this->name . ($this->plugin ? '.' . $this->plugin : '');
$table = TableRegistry::get($tableName);
$primaryKey = $table->aliasField($table->getPrimaryKey());
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
    <search
        :display-fields='<?= json_encode($this->Search->getDisplayFields($tableName)) ?>'
        filters='<?= json_encode($this->Search->getFilters($tableName)) ?>'
        id="<?= $searchId ?>"
        model="<?= $this->name ?>"
        primary-key="<?= $primaryKey ?>"
        user-id="<?= User::getCurrentUser()['id'] ?>"
        :with-form='false'
    ></search>
</section>