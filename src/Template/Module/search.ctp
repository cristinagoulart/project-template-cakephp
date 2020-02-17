<?php
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;

$this->Html->script(['/dist/vendor', '/dist/app'], ['block' => 'scriptBottom']);
$this->Html->css('/dist/style', ['block' => 'css']);

$tableName = $this->name . ($this->plugin ? '.' . $this->plugin : '');
$table = TableRegistry::getTableLocator()->get($tableName);

$accessFactory = new AccessFactory();
$urlBatch = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'];
$urlExport = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'exportSearch'];
?>
<section class="content-header"></section>
<section class="content">
    <search
        :display-fields='<?= h(json_encode($this->Search->getDisplayFields($tableName))) ?>'
        :filters='<?= h(json_encode($this->Search->getFilters($tableName))) ?>'
        id="<?= $searchId ?>"
        search-query="<?= '' !== $searchId ? '' : $searchQuery ?>"
        model="<?= $this->name ?>"
        primary-key="<?= $table->aliasField($table->getPrimaryKey()) ?>"
        user-id="<?= User::getCurrentUser()['id'] ?>"
        :with-batch-delete="<?= $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
        :with-batch-edit="<?= $accessFactory->hasAccess($urlBatch, $user) ? 'true' : 'false' ?>"
        :with-export="<?= $accessFactory->hasAccess($urlExport, $user) ? 'true' : 'false' ?>"
    ></search>
</section>
