<?php
use Cake\Core\Configure;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/config/bootstrap.php';

// set Modules path to test configuration
Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);
