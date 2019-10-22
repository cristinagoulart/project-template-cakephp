<?php
use Cake\Utility\Hash;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RolesCapabilities\Access\AccessFactory;

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parseToArray();

if (! Hash::get($config, 'table.searchable')) {
    return;
}

$url = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'search'];
if (! (new AccessFactory())->hasAccess($url, $user)) {
    return;
}

echo $this->element('search-form', ['name' => Hash::get($config, 'table.alias', $this->name)]);
