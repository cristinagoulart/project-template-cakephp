<?php
return [
    'LogActions' => [
        // List of controllers to Log
        'controllers' => ['Things'],
        // Actions of the controllers to log
        'actions' => ['view', 'edit'],
        // Enable file logging instead of DB
        'log_to_file' => false
    ]
];
