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

use RolesCapabilities\Access\AccessFactory;

?>
<nav class="navbar navbar-static-top" role="navigation">
    <?php if (!empty($user)) : ?>
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <li><?= $this->element('aside/form') ?></li>
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?= $this->Html->tag('img', false, ['src' => $user['image_src'], 'class' => 'user-image']) ?>
                    <span class="hidden-xs"><?= $user['name']; ?></span>
                </a>
                <ul class="dropdown-menu">
                    <!-- User image -->
                    <li class="user-header">
                        <?= $this->Html->tag('img', false, ['src' => $user['image_src'], 'class' => 'img-circle']) ?>
                        <p>
                            <?= $user['name']; ?>
                            <small><?= __('Member since {0}', $user['created']->i18nFormat('LLLL yyyy')) ?></small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <div class="pull-left">
                            <?php
                                $factory = new AccessFactory();
                                if($factory->hasAccess(['controller' => 'Users', 'action' => 'profile'], $user)):
                            ?>
                                <?= $this->Html->link(
                                '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . __('Profile'),
                                '/users/profile',
                                ['class' => 'btn btn-default btn-flat', 'escape' => false]
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="pull-right">
                            <?= $this->Html->link(
                                '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> ' . __('Sign out'),
                                '/users/logout',
                                ['class' => 'btn btn-default btn-flat', 'escape' => false]
                            ); ?>
                        </div>
                    </li>
                </ul>
            </li>
            <!-- Control Sidebar Toggle Button -->
            <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</nav>
