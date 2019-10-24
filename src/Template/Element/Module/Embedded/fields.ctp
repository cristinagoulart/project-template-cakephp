<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\Utility\Field;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

// Fetch embedded module(s)
foreach ($fields as $field) {
    $fieldName = substr($field, strrpos($field, '.') + 1);

    if (!$options['entity']->get($fieldName)) {
        continue;
    }

    $associationName = '';
    foreach ($table->associations() as $association) {
        if ('manyToOne' !== $association->type()) {
            continue;
        }
        if ($association->getForeignKey() !== $fieldName) {
            continue;
        }

        $associationName = Inflector::underscore(Inflector::singularize($association->getName()));
    }

    if ('' === $associationName) {
        continue;
    }

    $tableName = substr($field, 0, strrpos($field, '.'));
    list(, $relatedModule) = pluginSplit($tableName);
    $relatedTable = TableRegistry::getTableLocator()->get($tableName);
    $relatedEntity = $relatedTable->find()
        ->where([$relatedTable->getPrimaryKey() => $options['entity']->get($fieldName)])
        ->first();

    if (null === $relatedEntity) {
        continue;
    }

    $config = (new ModuleConfig(ConfigType::MODULE(), $relatedModule))->parseToArray();

    echo $this->element('Module/View/fields', [
        'table' => $relatedTable,
        'panelPrefix' => Hash::get($config, 'table.alias', Inflector::singularize(Inflector::humanize($relatedModule))) . ': ',
        'options' => [
            'entity' => $relatedEntity,
            'fields' => Field::getCsvView($relatedTable, 'view', true, true)
        ]
    ]);
}
