<?php
// Burzum File-Storage plugin configuration
return [
    'FileStorage' => [
        'defaultImageSize' => getenv('DEFAULT_IMAGE_SIZE') ? getenv('DEFAULT_IMAGE_SIZE') : 'huge',
    ],
];
