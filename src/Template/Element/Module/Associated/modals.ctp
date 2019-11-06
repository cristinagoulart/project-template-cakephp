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
use Cake\ORM\TableRegistry;

$associations = [];
$associationsCheck = false;
$fieldName = '';
?>
<?php foreach ($fields as $field) : ?>
    <?php
    if (!$associationsCheck) {
        $table = TableRegistry::getTableLocator()->get($field['model']);
        $associations = $table->associations();
        $associationsCheck = true;
    }

    if (empty($associations)){
        return;
    }

    foreach ($associations as $association) {
        if ($association->getName() == $field['name']) {
            $fieldName = Inflector::underscore($association->className() . '_' . $association->getForeignKey());

            $modalBody = $this->element('Module/Embedded/form', [
                'model' => $association->className(),
                'field' => $association->getForeignKey(),
                'associationName' => $association->getName(),
                'relatedModel' => Inflector::delimit($this->request->getParam('controller'), '-'),
                'relatedId' => $this->request->getParam('pass.0')
            ]);

            break;
        }
    }

    if (empty($fieldName)) {
        return;
    }
    ?>
<!-- Modal -->
<div id="<?= $fieldName ?>_modal_association" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body"><?= $modalBody ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>
