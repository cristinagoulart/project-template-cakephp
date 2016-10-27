<?php
use Cake\Core\Configure;
$ua = isset($ua) ? $ua : Configure::read('App.analytics');
?>
<?php if (!empty($ua)) : ?>
    <!-- Google Analytics -->
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', '<?= $ua ?>', 'auto');
    ga('send', 'pageview');
    </script>
    <!-- End Google Analytics -->
<?php endif; ?>