<?php
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RolesCapabilities\Access\AccessFactory;

if (! $options['entity']->get($field['name'])) {
    return;
}

$isTranslatable = function ($tableName, $fieldName) {
    // Read translatable from config.ini
    $mc = new ModuleConfig(ConfigType::MODULE(), Inflector::camelize($tableName));
    $config = $mc->parse();

    if (!(bool)$config->table->translatable) {
        return false;
    }

    // Read field options from fields.ini
    $mc = new ModuleConfig(ConfigType::FIELDS(), Inflector::camelize($tableName));
    $config = $mc->parse();

    if (!isset($config->{$fieldName}->translatable)) {
        return false;
    }

    return (bool)$config->{$fieldName}->translatable;
};

if (! $isTranslatable($tableName, $field['name'])) {
    return;
}

$accessFactory = new AccessFactory();
$url = ['plugin' => 'Translations', 'controller' => 'Translations', 'action' => 'addOrUpdate'];
if (! $accessFactory->hasAccess($url, $user)) {
    return;
}

echo $this->Html->link(
    '<i class="fa fa-globe"></i>',
    '#translations_translate_id_modal',
    [
        'data-toggle' => 'modal',
        'data-record' => $options['entity']->get('id'),
        'data-model' => $tableName,
        'data-field' => $field['name'],
        'data-value' => $options['entity']->get($field['name']),
        'escape' => false
    ]
);
?>&nbsp;
<?= $this->element('Translations.modal_add') ?>