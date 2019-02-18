<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\User;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$tableName = $this->name . ($this->plugin ? '.' . $this->plugin : '');
$table = TableRegistry::get($tableName);
$primaryKey = $table->aliasField($table->getPrimaryKey());
$searchId = $this->request->getParam('pass.0', '');
?>
<section class="content-header">
    <h4><?= __('Search') ?></h4>
</section>
<section class="content">
    <search
        :display-fields='<?= json_encode('' === $searchId ? $this->Search->getDisplayFields($tableName) : []) ?>'
        filters='<?= json_encode($this->Search->getFilters($tableName)) ?>'
        id="<?= $searchId ?>"
        model="<?= $this->name ?>"
        primary-key="<?= $primaryKey ?>"
        user-id="<?= User::getCurrentUser()['id'] ?>"
    ></search>
</section>