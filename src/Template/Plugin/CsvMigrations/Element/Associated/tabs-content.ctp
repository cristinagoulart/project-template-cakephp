<?php
use Cake\Utility\Inflector;
use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();
?>
<div class="tab-content">
    <?php $active = 'active'; ?>
    <?php foreach ($associations as $association) : ?>
        <?php
        $url = [
            'prefix' => 'api',
            'controller' => $this->request->getParam('controller'),
            'action' => 'related',
            $entity->get($table->getPrimaryKey()),
            $association->getName()
        ];
        ?>
        <?php $containerId = Inflector::underscore($association->getAlias()); ?>
        <div role="tabpanel" class="tab-pane <?= $active ?>" id="<?= $containerId ?>">
            <?php
            list($plugin, $controller) = pluginSplit($association->className());
            $accessUrl = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'add'];
            if (in_array($association->type(), ['manyToMany']) && $accessFactory->hasAccess($accessUrl, $user)) {
                echo $this->element('CsvMigrations.Embedded/lookup', ['association' => $association, 'user' => $user]);
            } ?>
            <?= $this->element('CsvMigrations.Associated/tab-content', [
                'association' => $association, 'table' => $table, 'url' => $this->Url->build($url), 'factory' => $factory, 'containerId' => $containerId
            ]) ?>
        </div>
        <?php $active = ''; ?>
    <?php endforeach; ?>
</div> <!-- .tab-content -->
<?php
echo $this->Html->scriptBlock("
var tabClicked = false;
var activeTab = localStorage.getItem('activeTab_relatedTabs');

if (activeTab) {
    $('#relatedTabs li').each(function(key, element) {
        var link = $(this).find('a');
        if (activeTab == key) {
            tabClicked = true;
            $(link).click();
        }
    });
}
if (!tabClicked) {
    var activeTab = $('#relatedTabs li.active');
    if (activeTab) {
        $(activeTab).find('a').click();
    } else {
        $('#relatedTabs li').first().find('a').click();
    }
}
", ['block' => 'scriptBottom']);
?>
