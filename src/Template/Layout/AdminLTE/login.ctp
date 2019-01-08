<?php
use Cake\Core\Configure;
use Cake\Filesystem\Folder;

$this->Html->css('login', ['block' => 'css']);

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
        <?php echo $this->Html->css($skinUrl); ?>

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
            $path = '/img/login/' . (string)Configure::read('Theme.backgroundImages') . '/';
            $images = (new Folder(WWW_ROOT . $path))->find('.*\.png|jpg|jpeg', true);

            if (! empty($images)) {
                echo $this->Html->tag('style', sprintf(
                    '.login-page {%s}',
                    $this->Html->style(['background-image' => 'url(' . $path . '/' . $images[array_rand($images)] . ')'])
                ));
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

        <?php echo $this->Html->script('AdminLTE./bower_components/jquery/dist/jquery.min'); ?>
        <?php echo $this->Html->script('AdminLTE./bower_components/bootstrap/dist/js/bootstrap.min'); ?>
        <?php echo $this->Html->script('AdminLTE./bower_components/jquery-slimscroll/jquery.slimscroll.min'); ?>
        <?php echo $this->Html->script('AdminLTE./bower_components/fastclick/lib/fastclick'); ?>
        <!-- AdminLTE App -->
        <?php echo $this->Html->script('AdminLTE./js/adminlte.min'); ?>

        <?php echo $this->fetch('script'); ?>
        <?php echo $this->fetch('scriptBottom'); ?>

        <script type="text/javascript">
            $(document).ready(function(){
                $(".navbar .menu").slimscroll({
                    height: "200px",
                    alwaysVisible: false,
                    size: "3px"
                }).css("width", "100%");

                var a = $('a[href="<?php echo $this->request->getAttribute('webroot') . $this->request->getPath() ?>"]');
                if (!a.parent().hasClass('treeview') && !a.parent().parent().hasClass('pagination')) {
                    a.parent().addClass('active').parents('.treeview').addClass('active');
                }
            });
        </script>
    </body>
</html>
