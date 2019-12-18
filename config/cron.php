<?php

return [
    'Cron' => [
        'CakeShell' => [
            'skipFiles' => [
                'ConsoleShell',
                'FakerShell',
                'PluginShell',
                'CronShell',
                'FixDateTimeShell',
                'GenerateLanguageFilesShell',
            ],
            'skipPlugins' => [
                'Bake',
            ],
        ],
    ],
];
