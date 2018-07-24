<?php
use App\Avatar\Type\Gravatar;
use App\Avatar\Type\ImageSource;
use App\Avatar\Type\DynamicAvatar;

return [
    'Avatar' => [
        'default' => Gravatar::class,
        'options' => [
            ImageSource::class => [
                'src' => '/img/user-image-160x160.png' // sets the default/fallback image
            ],
            Gravatar::class => [
                'size' => 160, // sets the desired image size
                'default' => 'mm', // sets the default/fallback themed image
                'rating' => 'g', // sets the desired image appropriateness rating
            ],
            DynamicAvatar::class => [
                'size' => 160,
                'length' => 2,
                'background' => '#00c0ef',
            ]
        ]
    ]
];
