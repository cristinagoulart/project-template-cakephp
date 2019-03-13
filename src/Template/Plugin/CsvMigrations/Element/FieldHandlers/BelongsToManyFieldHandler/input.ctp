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

use Cake\Utility\Inflector;
use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();
list($plugin, $controller) = pluginSplit($association->className());

$linkUrl=[
    'plugin' => $plugin,
    'action' => 'link',
    'controller' => $controller
];

$unLinkUrl=[
    'plugin' => $plugin,
    'action' => 'unlink',
    'controller' => $controller
];

//Check if it has access for link or unlink
if (!$accessFactory->hasAccess($linkUrl, $user) || !$accessFactory->hasAccess($unLinkUrl, $user)) {
    return;
}

$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'label' => false,
    'id' => $association->className(),
    'type' => $type,
    'options' => $options,
    'value' => $value,
    'title' => $title,
    'data-type' => 'select2',
    'data-display-field' => $relatedProperties['displayField'],
    'escape' => false,
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'multiple' => 'multiple',
    'data-url' => $this->Url->build([
        'prefix' => 'api',
        'plugin' => $relatedProperties['plugin'],
        'controller' => $relatedProperties['controller'],
        'action' => 'lookup.json'
    ]),
];

$modalId = Inflector::underscore($association->className() . '_' . $association->getForeignKey());
?>
<?= $this->Form->label($name, $label, ['class' => 'control-label']) ?>
<div class="input-group select2-bootstrap-prepend select2-bootstrap-append">
    <span class="input-group-addon" title="<?= $relatedProperties['controller'] ?>">
        <span class="fa fa-<?= $icon ?>"></span>
    </span>
        <?= $this->Form->control($association->getTarget()->aliasField('_ids'), $attributes); ?>
    <?php if ($accessFactory->hasAccess(['action' => 'add', 'controller' => $association->className()], $user)) { ?>
        <div class="input-group-btn">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#<?= $modalId ?>_modal_association">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
        </div>
    <?php } ?>
</div>