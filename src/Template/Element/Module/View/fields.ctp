<?php
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);

// row field count with most fields
$fieldCountMax = 1;
foreach ($options['fields'] as $panelFields) {
    foreach ($panelFields as $subFields) {
        if (count($subFields) > $fieldCountMax) {
            $fieldCountMax = count($subFields);
        }
    }
}

$embeddedFields = [];
foreach ($options['fields'] as $panelName => $panelFields) : ?>
    <?php
    if ($this->request->getQuery('embedded')) {
        $panelName = Inflector::singularize(Inflector::humanize($this->name)) . ': ' . $panelName;
    }
    ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= isset($panelPrefix) ? $panelPrefix . $panelName : $panelName ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
        <?php foreach ($panelFields as $subFields) : ?>
            <div class="row">
            <?php foreach ($subFields as $field) : ?>
                <?php $fieldCount = 12 < count($subFields) ? 12 : count($subFields); ?>
                <?php if ('' === trim($field['name'])) : ?>
                    <div class="col-xs-4 col-md-2 text-right">&nbsp;</div>
                    <div class="col-xs-8 col-md-4">&nbsp;</div>
                    <?php continue; ?>
                <?php endif; ?>
                <?php
                // embedded field detection
                preg_match(CsvField::PATTERN_TYPE, $field['name'], $matches);

                if (! empty($matches[1]) && 'EMBEDDED' === $matches[1]) {
                    $embeddedFields[] = $matches[2];
                    continue;
                }

                echo $this->element('Module/Field/value', [
                    'factory' => $factory,
                    'field' => $field,
                    'options' => $options,
                    'fieldCount' => $fieldCount,
                    'fieldCountMax' => $fieldCountMax
                ]);
                ?>
                <div class="clearfix visible-xs visible-sm"></div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php
    if ([] === $embeddedFields) {
        continue;
    }

    echo $this->element('Module/Embedded/fields', [
        'fields' => $embeddedFields, 'table' => $table, 'options' => $options
    ]);

    $embeddedFields = [];
    ?>
<?php endforeach; ?>
