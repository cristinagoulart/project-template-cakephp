<?php
return [
    'Avatar' => [
        'default' => App\Avatar\Type\Gravatar::class,
        'defaultImage' => '/img/user-image-160x160.png', // sets the default/fallback image
        'directory' => DS . 'uploads' . DS . 'avatars' . DS,
        'customDirectory' => DS . 'uploads' . DS . 'avatars' . DS . 'custom' . DS,
        'extension' => '.png',
        'fileSize' => 524288,
        'options' => [
            App\Avatar\Type\ImageSource::class => [],
            App\Avatar\Type\Gravatar::class => [
                'size' => 160, // sets the desired image size
                'default' => '404', // sets the default/fallback themed image
                'rating' => 'g', // sets the desired image appropriateness rating
            ],
            App\Avatar\Type\DynamicAvatar::class => [
                'size' => 160,
                'length' => 2,
                'background' => ['#00c0ef', '#00a65a', '#d81b60', '#605ca8', '#39CCCC', '#f56954'],
            ],
        ],
        'order' => [
            App\Avatar\Type\ImageSource::class,
            App\Avatar\Type\Gravatar::class,
            App\Avatar\Type\DynamicAvatar::class,
        ],
    ],
];
