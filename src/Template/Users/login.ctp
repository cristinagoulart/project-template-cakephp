<?php
use Cake\Core\Configure;

$this->layout = 'AdminLTE/login';

$element = 'login-' . (string)Configure::read('Theme.version');
if (! $this->elementExists($element)) {
    $element = 'login-light';
}

echo $this->element($element);
