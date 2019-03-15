<?php
deprecationWarning('"App.Search/Forms/save_search" element is deprecated.');

use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();

$url = [
    'plugin' => $this->request->getParam('plugin'),
    'controller' => $this->request->getParam('controller'),
    'action' => 'saveSearch'
];

if ($accessFactory->hasAccess($url, $user)) : ?>
    <?php
    echo $this->Form->label(__('Save search'));

    echo $this->Form->create(null, [
        'class' => 'save-search-form',
        'url' => [
            'plugin' => $this->request->getParam('plugin'),
            'controller' => $this->request->getParam('controller'),
            'action' => ($savedSearch->get('is_editable') ? 'edit': 'save') . '-search',
            $preSaveId,
            $savedSearch->get('is_editable') ? $savedSearch->id : null
        ]
    ]); ?>
    <div class="input-group">
        <?= $this->Form->control('name', [
            'label' => false,
            'class' => 'form-control input-sm',
            'placeholder' => 'Save criteria name',
            'required' => true,
            'value' => $savedSearch->get('is_editable') ? $savedSearch->name : ''
        ]); ?>
        <span class="input-group-btn">
            <?= $this->Form->button(
                '<i class="fa fa-floppy-o"></i>',
                ['class' => 'btn btn-sm btn-primary']
            ) ?>
        </span>
    </div>
    <?= $this->Form->end(); ?>
<?php endif; ?>
