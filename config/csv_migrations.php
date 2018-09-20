<?php
use Cake\Core\Configure;

// CsvMigrations plugin configuration
return [
    'CsvMigrations' => [
        'tableValidation' => false,
        'api' => [
            'auth' => Configure::read('API.auth')
        ]
    ]
];
