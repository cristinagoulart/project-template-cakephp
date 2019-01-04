<?php
use Cake\Core\Configure;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use RolesCapabilities\Access\AccessFactory;

// skip non-csv modules
if (! in_array($this->name, Utility::findDirs(Configure::read('CsvMigrations.modules.path')))) {
    return;
}

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();

// skip non-searchable modules
if (! (bool)$config->table->searchable) {
    return;
}

if (! (new AccessFactory())->hasAccess(
    ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'search'],
    $user
)) {
    return;
}

echo $this->element('search-form', ['name' => isset($config->table->alias) ? $config->table->alias : $this->name]);
