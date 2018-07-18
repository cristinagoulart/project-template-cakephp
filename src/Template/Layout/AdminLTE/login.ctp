<?php
use Cake\Core\Configure;

$this->Html->css('login-' . (string)(Configure::read('Theme.version') ?: 'light'), ['block' => 'css']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo Configure::read('Theme.title.' . $this->name); ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <?php echo $this->Html->css('AdminLTE./bootstrap/css/bootstrap.min'); ?>
        <?php echo $this->Html->css('/plugins/font-awesome/css/font-awesome.min'); ?>
        <?php echo $this->Html->css('/plugins/ionicons/css/ionicons.min'); ?>
        <!-- Theme style -->
        <?php echo $this->Html->css('AdminLTE.AdminLTE.min'); ?>
        <?php echo $this->Html->css('AdminLTE.skins/skin-' . Configure::read('Theme.skin') . '.min'); ?>

        <?php echo $this->fetch('css'); ?>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition skin-<?php echo Configure::read('Theme.skin'); ?> login-page">
        <?php
        if ('dark' == Configure::read('Theme.version')) {
            echo $this->element('background-image-dark');
        }
        ?>
        <div class="login-box">
            <div class="login-logo">
                <a href="<?php echo $this->Url->build('/'); ?>"><?php echo $theme['logo']['large'] ?></a>
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body">
                <p> <?php echo $this->Flash->render(); ?> </p>
                <p> <?php echo $this->Flash->render('auth'); ?> </p>

                <?php echo $this->fetch('content'); ?>
            </div>
            <!-- /.login-box-body -->
        </div>
        <!-- /.login-box -->

        <?php echo $this->Html->script('AdminLTE./plugins/jQuery/jquery-2.2.3.min'); ?>
        <?php echo $this->Html->script('AdminLTE./bootstrap/js/bootstrap.min'); ?>
        <?php echo $this->Html->script('AdminLTE./plugins/slimScroll/jquery.slimscroll.min'); ?>
        <?php echo $this->Html->script('AdminLTE./plugins/fastclick/fastclick.min'); ?>
        <!-- AdminLTE App -->
        <?php echo $this->Html->script('AdminLTE./js/app.min'); ?>

        <?php echo $this->fetch('script'); ?>
        <?php echo $this->fetch('scriptBottom'); ?>

        <script type="text/javascript">
            $(document).ready(function(){
                $(".navbar .menu").slimscroll({
                    height: "200px",
                    alwaysVisible: false,
                    size: "3px"
                }).css("width", "100%");

                var a = $('a[href="<?php echo $this->request->webroot . $this->request->url ?>"]');
                if (!a.parent().hasClass('treeview') && !a.parent().parent().hasClass('pagination')) {
                    a.parent().addClass('active').parents('.treeview').addClass('active');
                }
            });
        </script>
    </body>
</html>
