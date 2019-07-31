<?php
use Cake\Console\ConsoleErrorHandler;
use Cake\Core\Configure;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

restore_error_handler();
// re-enable deprecation errors to be shown on the CLI during PHPunit execution.
Configure::write('Error.errorLevel', E_ALL);
// re-register application error and exception handlers.
(new ConsoleErrorHandler(Configure::read('Error')))->register();
