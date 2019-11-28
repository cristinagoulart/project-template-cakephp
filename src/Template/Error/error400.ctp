<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\Routing\Router;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.ctp');

    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params: </strong>
        <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?= $this->element('auto_table_warning') ?>
<?php
if (extension_loaded('xdebug')) :
    xdebug_print_function_stack();
endif;

$this->end();
endif;
?>
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-danger box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    <?= __d('cake', 'Error') ?> <?= h($code) ?>: <?= h($message) ?>
                </h3>
            </div>
            <div class="box-body">
                <?php
                    switch ($code) {
                        case 401: echo __('The page that you are trying to access requires authorization. Please login and try again.'); break;
                        case 403: echo __('You do not have sufficient authorization to access this page. Please contact your system administrator.'); break;
                        case 404: echo __('The page that you are trying to access does not exist. Please adjust your URL and try again.'); break;
                        case 405: echo __('The method that you are using to access this page is not allowed.'); break;
                        case 406: echo __('Your browser does not understand how to render the requested page. Please contact your system administrator.'); break;
                        case 408: echo __('The server took longer than it is allowed to process your request.  Please try again later.'); break;
                        case 409: echo __('The file that you requested used to be here, but it is gone now.  Please contact your system administrator.'); break;
                        case 411: echo __('Your request is missing <pre>Content-Length</pre> header. Please contact your system administrator.'); break;
                        case 413: echo __('The request file was too big to process.  Please try with a smaller file or contact your system administrator.'); break;
                        case 414: echo __('The request URL is too long.  Please limit the number and/or length of parameters, or contact your system administrator.'); break;
                        case 415: echo __('The file type of the request is not supported.  Please try with another file or contact your system administrator.'); break;
                        default: echo __('There was a problem with your request. Please try later, adjust your request, or contact your system administrator.'); break;
                    }
                ?>
            </div>
            <div class="box-footer">
                <b>URL: </b><?= h(Router::url($url, true)) ?>
            </div>
        </div>
    </div>
</div>
