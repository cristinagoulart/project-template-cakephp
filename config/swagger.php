<?php

use App\Swagger\Analyser;
use Cake\Core\Configure;

return [
    'Swagger' => [
        'ui' => [
            'title' => (getenv('PROJECT_NAME') ?: basename(ROOT)) . ': API Documentation',
            'validator' => true,
            'api_selector' => true,
            'route' => '/swagger/',
            'schemes' => ['http', 'https'],
        ],
        'docs' => [
            'crawl' => (bool)Configure::read('debug') || (bool)Configure::read('Swagger.crawl'),
            'route' => '/swagger/docs/',
            'cors' => [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST',
                'Access-Control-Allow-Headers' => 'X-Requested-With',
            ],
        ],
        'library' => [
            'api' => [
                'include' => [
                    ROOT . DS . 'src' . DS . 'Controller' . DS . 'Api' . DS . 'V1' . DS . 'V0',
                ],
            ],
        ],
        'analyser' => new Analyser(),
    ],
];
