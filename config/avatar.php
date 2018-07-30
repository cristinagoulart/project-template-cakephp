<?php
return [
    'Avatar' => [
        'default' => 'Gravatar',
        'defaultImage' => '/img/user-image-160x160.png', // sets the default/fallback image
        'directory' => '/uploads' . DS . 'avatars' . DS,
        'customDirectory' => '/uploads' . DS . 'avatars' . DS . 'custom' . DS,
        'extension' => '.png',
        'fileSize' => 524288,
        'options' => [
            'App\Avatar\Type\ImageSource' => [],
            'App\Avatar\Type\Gravatar' => [
                'size' => 160, // sets the desired image size
                'default' => '404', // sets the default/fallback themed image
                'rating' => 'g', // sets the desired image appropriateness rating
            ],
            'App\Avatar\Type\DynamicAvatar' => [
                'size' => 160,
                'length' => 2,
                'background' => '#00c0ef',
            ]
        ],
        'order' => [
            App\Avatar\Type\ImageSource::class,
            App\Avatar\Type\Gravatar::class,
            App\Avatar\Type\DynamicAvatar::class,
        ]
    ]
];
