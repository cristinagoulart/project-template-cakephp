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
?>
<?php foreach ($fields as $field) : ?>
    <?php
    $rpos = strrpos($field['name'], '.');
    $fieldName = substr($field['name'], $rpos + 1);
    $tableName = substr($field['name'], 0, $rpos);
    $modalBody = $this->element('Module/Embedded/form', ['model' => $tableName, 'field' => $fieldName]);
    ?>
<!-- Modal -->
<div id="<?= $fieldName ?>_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body"><?= $modalBody ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>
