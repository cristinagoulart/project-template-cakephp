<?php
// DatabaseLog plugin configuration
return [
    'DatabaseLog' => [
        'maxLength' => '-1 month',
        // Display styles for log listing
        'typeStyles' => [
            'emergency' => [
                'button' => 'btn btn-danger',
                'icon' => 'fa fa-exclamation-triangle bg-red',
                'header' => 'bg-danger',
            ],
            'alert' => [
                'button' => 'btn btn-danger',
                'icon' => 'fa fa-exclamation-triangle bg-red',
                'header' => 'bg-danger',
            ],
            'critical' => [
                'button' => 'btn btn-danger',
                'icon' => 'fa fa-exclamation-triangle bg-red',
                'header' => 'bg-danger',
            ],
            'error' => [
                'button' => 'btn btn-danger',
                'icon' => 'fa fa-exclamation-triangle bg-red',
                'header' => 'bg-danger',
            ],
            'warning' => [
                'button' => 'btn btn-warning',
                'icon' => 'fa fa-exclamation-triangle bg-yellow',
                'header' => 'bg-warning',
            ],
            'notice' => [
                'button' => 'btn btn-info',
                'icon' => 'fa fa-info-circle bg-aqua',
                'header' => 'bg-info',
            ],
            'info' => [
                'button' => 'btn btn-info',
                'icon' => 'fa fa-info-circle bg-aqua',
                'header' => 'bg-info',
            ],
            'debug' => [
                'button' => 'btn bg-gray',
                'icon' => 'fa fa-wrench bg-gray',
                'header' => 'bg-gray',
            ],
        ],
        // Settings for the log stats shell
        'stats' => [
            'period' => '-1 day',
            'limit' => 10,
        ]
    ]
];
