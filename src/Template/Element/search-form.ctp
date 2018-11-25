<?php
use Cake\Utility\Inflector;

$formOptions = [
    'class' => 'navbar-form navbar-left search-form-top-menu',
    'url' => [
        'plugin' => $this->request->getParam('plugin'),
        'controller' => $this->request->getParam('controller'),
        'action' => 'search'
    ]
];

if (!isset($name)) {
    $name = $this->name;
}

$name = Inflector::humanize(Inflector::underscore($name));

$inputOptions = [
    'label' => false,
    'div' => false,
    'container' => false,
    'class' => 'form-control input-sm',
    'placeholder' => 'Search in ' . strtolower($name) . '...',
    'templates' => [
        'inputContainer' => '{{content}}'
    ]
];
?>
<?= $this->Form->create(null, $formOptions); ?>
<div class="input-group">
<?= $this->Form->control('criteria[query]', $inputOptions); ?>
    <span class="input-group-btn">
        <?= $this->Form->button('<i class="fa fa-search"></i>', ['class' => 'btn btn-sm']); ?>
    </span>
</div>
<?= $this->Form->end(); ?>
