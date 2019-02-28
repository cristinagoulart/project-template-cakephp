<?php
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$mc = new ModuleConfig(ConfigType::MODULE(), $this->name);
$config = $mc->parse();

$labels = (array)$config->associationLabels;
$setLabels = [];
?>
<ul id="relatedTabs" class="nav nav-tabs responsive-tabs" role="tablist">
    <?php $active = 'active'; ?>
    <?php foreach ($associations as $association) : ?>
        <?php
        $containerId = Inflector::underscore($association->getAlias());

        list(, $tableName) = pluginSplit($association->className());
        $mc = new ModuleConfig(ConfigType::MODULE(), $tableName);
        $config = $mc->parse();

        $label = '<span class="fa fa-' . $config->table->icon . '"></span> ';

        if (array_key_exists($association->getAlias(), $labels)) {
            $label .= $labels[$association->getAlias()];
        } else {
            $label .= isset($config->table->alias) ?
                $config->table->alias :
                Inflector::humanize(Inflector::delimit($tableName));
        }

        if (in_array($label, $setLabels)) {
            $label .= ' (' . $association->getForeignKey() . ')';
        }

        $setLabels[] = $label;
        ?>
        <li role="presentation" class="<?= $active ?>">
            <?= $this->Html->link($label, '#' . $containerId, [
                'role' => 'tab', 'data-toggle' => 'tab', 'escape' => false, 'class' => $containerId
            ]);?>
        </li>
        <?php $active = ''; ?>
    <?php endforeach; ?>
</ul>
