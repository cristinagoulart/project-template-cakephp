<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;

$cakeDescription = 'CakePHP: the rapid development php framework';

$skinUrl = Configure::read('Theme.skinUrl');
$skinName = Configure::read('Theme.skin');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo Configure::read('Theme.title.' . $this->name); ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php echo $this->Html->css('AdminLTE./bower_components/bootstrap/dist/css/bootstrap.min'); ?>
    <?php echo $this->Html->css('/plugins/font-awesome/css/font-awesome.min'); ?>
    <?php echo $this->Html->css('/plugins/ionicons/css/ionicons.min'); ?>
    <!-- Theme style -->
    <?php echo $this->Html->css('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic'); ?>
    <?php echo $this->Html->css('AdminLTE.AdminLTE.min'); ?>
    <?php echo $this->Html->css($skinUrl);?>

    <?php echo $this->fetch('css'); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>


<body class="hold-transition skin-<?php echo Configure::read('Theme.skin'); ?> sidebar-mini error-container">

    <div class="wrapper">
        <header class="main-header">
            <!-- Logo -->
            <a href="<?php echo $this->Url->build('/'); ?>" class="logo">
              <!-- mini logo for sidebar mini 50x50 pixels -->
              <span class="logo-mini"><?= $this->element('logo', ['size' => 'mini']) ?></span>
              <!-- logo for regular state and mobile devices -->
              <span class="logo-lg"><?= $this->element('logo') ?></span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <?php echo $this->element('nav-top') ?>
        </header>


        <div class="content-wrapper">
            <div class="container" style="margin:0 auto;">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>

        <?php echo $this->element('footer'); ?>

        <!-- Control Sidebar -->
        <?php //echo $this->element('aside-control-sidebar'); ?>

        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
        immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>

    <?php echo $this->Html->script('AdminLTE./bower_components/jquery/dist/jquery.min'); ?>
    <?php echo $this->Html->script('AdminLTE./bower_components/bootstrap/dist/js/bootstrap.min'); ?>
    <?php echo $this->Html->script('AdminLTE./bower_components/jquery-slimscroll/jquery.slimscroll.min'); ?>
    <?php echo $this->Html->script('AdminLTE./bower_components/fastclick/lib/fastclick'); ?>
    <!-- AdminLTE App -->
    <?php echo $this->Html->script('AdminLTE./js/adminlte.min'); ?>

    <?php echo $this->fetch('script'); ?>
    <?php echo $this->fetch('scriptBottom'); ?>
</body>
</html>
