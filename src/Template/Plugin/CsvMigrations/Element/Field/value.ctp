<?php
$tableName = $field['model'];
if ($field['plugin']) {
    $tableName = $field['plugin'] . '.' . $tableName;
}

$renderOptions = ['entity' => $options['entity'], 'imageSize' => 'small'];

$label = $factory->renderName($tableName, $field['name'], $renderOptions);
$value = $factory->renderValue($tableName, $field['name'], $options['entity'], $renderOptions);
$value = empty($value) ? '&nbsp;' : $value;

// append translation modal button
$value .= $this->element('Module/Menu/translations', [
    'options' => $options,
    'field' => $field,
    'tableName' => $tableName
]);
?>
<div class="col-xs-4 col-md-2 text-right"><strong><?= $label ?>:</strong></div>
<div class="col-xs-8 col-md-4"><?= $value ?></div>