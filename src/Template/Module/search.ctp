<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\User;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$tableName = $this->name . ($this->plugin ? '.' . $this->plugin : '');
$table = TableRegistry::get($tableName);
?>
<section class="content-header"></section>
<section class="content">
    <search
        :display-fields='<?= json_encode($this->Search->getDisplayFields($tableName)) ?>'
        filters='<?= json_encode($this->Search->getFilters($tableName)) ?>'
        id="<?= $this->request->getParam('pass.0', '') ?>"
        model="<?= $this->name ?>"
        primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
        user-id="<?= User::getCurrentUser()['id'] ?>"
    ></search>
</section>