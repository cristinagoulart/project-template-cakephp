<?php
use Cake\Core\Configure;

$this->layout = 'AdminLTE/login';

$element = 'register-' . (string)Configure::read('Theme.version');
if (! $this->elementExists($element)) {
    $element = 'register-light';
}

echo $this->element($element);
