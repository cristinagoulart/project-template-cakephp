<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$tableName = $this->name . ($this->plugin ? '.' . $this->plugin : '');
$table = TableRegistry::get($tableName);

$accessFactory = new AccessFactory();
$urlBatch = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'];
?>
<section class="content-header"></section>
<section class="content">
    <search
        :display-fields='<?= json_encode($this->Search->getDisplayFields($tableName)) ?>'
        filters='<?= json_encode($this->Search->getFilters($tableName)) ?>'
        id="<?= $searchId ?>"
        search-query="<?= '' !== $searchId ? '' : $searchQuery ?>"
        model="<?= $this->name ?>"
        primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
        user-id="<?= User::getCurrentUser()['id'] ?>"
        :with-batch-delete="<?= $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
        :with-batch-edit="<?= $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
    ></search>
</section>
