<?php
use Cake\ORM\Association;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();

$mc = new ModuleConfig(ConfigType::MODULE(), $this->name);
$config = $mc->parse();

$hiddenAssociations = (array)$config->associations->hide_associations;

$associations = [];
foreach ($table->associations() as $association) {
    list($plugin, $controller) = pluginSplit($association->className());
    $url = ['plugin' => $plugin, 'controller' => $controller, 'action' => 'index'];
    // skip associations which current user has no access
    if (!$accessFactory->hasAccess($url, $user)) {
        continue;
    }

    // skip association(s) with Burzum/FileStorage, because it is rendered within the respective field handler
    if ('Burzum/FileStorage.FileStorage' === $association->className()) {
        continue;
    }

    // skip hidden associations
    if (in_array($association->getName(), $hiddenAssociations)) {
        continue;
    }

    if (!in_array($association->type(), [Association::MANY_TO_MANY, Association::ONE_TO_MANY])) {
        continue;
    }

    $associations[] = $association;
}

if (!empty($associations)) : ?>
    <?= $this->Html->scriptBlock(
        'var url = document.location.toString();
            if (matches = url.match(/(.*)(#.*)/)) {
                $(".nav-tabs a[href=\'" + matches["2"] + "\']").tab("show");
                history.pushState("", document.title, window.location.pathname + window.location.search);
            }
        ',
        ['block' => 'scriptBottom']
    ); ?>
    <div class="nav-tabs-custom">
        <?= $this->element('CsvMigrations.Associated/tabs-list', [
            'table' => $table, 'associations' => $associations
        ]); ?>
        <?= $this->element('CsvMigrations.Associated/tabs-content', [
            'table' => $table, 'associations' => $associations, 'factory' => $factory, 'entity' => $entity
        ]); ?>
    </div>
<?php endif ?>