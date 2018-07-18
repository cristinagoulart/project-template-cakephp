<?php
use Cake\Core\Configure;
use Cake\Filesystem\Folder;

$backgroundImages = '/img/login/' . Configure::read('Theme.backgroundImages') . '/';

$dir = new Folder(WWW_ROOT . $backgroundImages);
$images = $dir->find();

echo $this->Html->tag(
    'style',
    '.login-page {' . $this->Html->style(['background-image' => 'url(' . $backgroundImages . '/' . $images[array_rand($images)] . ')']) . '}'
);
?>
