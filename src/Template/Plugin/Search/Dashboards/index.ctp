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
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="jumbotron" style="border-radius: 3px; background: #ffffff; border-top: 3px solid #d2d6de; margin-top: 24px; padding: 24px;">
                <h1><?= __('Dashboards') ?></h1>
                <p><?= __('There are no configured Dashboards for you. Please contact the system administrator.') ?></p>
                <p><?= $this->Html->link(__('{0} Create Dashboard', '<i class="fa fa-plus"></i>'), ['action' => 'add'], ['class' => 'btn btn-primary', 'escape' => false]) ?><p>
            </div>
        </div>
    </div>
</div>
