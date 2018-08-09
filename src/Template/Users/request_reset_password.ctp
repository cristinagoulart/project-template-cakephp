<?php
use Cake\Core\Configure;

$this->layout = 'AdminLTE/login';

$element = 'reset-password-' . (string)Configure::read('Theme.version');
if (! $this->elementExists($element)) {
    $element = 'reset-password-light';
}

echo $this->element($element);
