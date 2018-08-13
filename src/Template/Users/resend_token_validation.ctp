<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Core\Configure;

$this->layout = 'AdminLTE/login';

$element = 'resend-token-validation-' . (string)Configure::read('Theme.version');
if (! $this->elementExists($element)) {
    $element = 'resend-token-validation-light';
}

echo $this->element($element);
