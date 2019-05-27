<?php
/**
 * AdminLTE plugin configuration
 *
 * Note: in case you want to use custom CSS skin,
 * you should set `skin` as `custom`
 * and defined `skinUrl` as `skins/skin-custom`
 * so it would be loaded locally.
 */

return [
    'Theme' => [
        'folder' => ROOT,
        'title' => getenv('PROJECT_NAME'),
        'logo' => [
            'mini' => 'logo-mini.png',
            'large' => 'logo.png',
        ],
        'login' => [
            'show_remember' => true,
            'show_register' => false,
            'show_social' => false,
        ],
        'skin' => 'blue',
        'skinUrl' => 'AdminLTE.skins/skin-blue.min',
        'backgroundImages' => 'qobo',
        'prependAvatars' => true,
    ],
];
