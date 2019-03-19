<?php
// Roles and Capabilities plugin configuration
return [
    'RolesCapabilities' => [
        'ownerCheck' => [
            // List of tables that should be skipped during record access check.
            'skipTables' => [
                'byInstance' => [
                    App\Model\Table\UsersTable::class,
                    Menu\Model\Table\MenuItemsTable::class,
                    Menu\Model\Table\MenusTable::class,
                    Search\Model\Table\SavedSearchesTable::class
                ]
            ],
        ],
        'accessCheck' => [
            'skipActions' => [
                'App\Controller\SystemController' => [
                    'error',
                    'home',
                ],
                'App\Controller\UsersController' => [
                    'changePassword',
                    'failedSocialLogin',
                    'failedSocialLoginListener',
                    'getUsersTable',
                    'login',
                    'logout',
                    'register',
                    'requestResetPassword',
                    'resendTokenValidation',
                    'resetPassword',
                    'setUsersTable',
                    'socialEmail',
                    'socialLogin',
                    'twitterLogin',
                    'validate',
                    'validateEmail',
                    'validateReCaptcha',
                ],
            ],
        ],
    ]
];
