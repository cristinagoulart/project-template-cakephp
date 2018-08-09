<?php

return [
    'Users.table' => 'Users',
    // Controller used to manage users plugin features & actions
    'controller' => 'Users',
    'Users.GoogleAuthenticator.login' => false,
    // disable remember-me functionality because currently it affects google authenticator functionality:
    // https://github.com/CakeDC/users/issues/488
    'Users.RememberMe.active' => false,
    // this is a workaround for disabling RememberMe functionality bug:
    // https://github.com/CakeDC/users/issues/490
    'Auth.authenticate' => [
        'all' => [
            'finder' => 'auth',
        ],
        'CakeDC/Users.ApiKey',
        // 'CakeDC/Users.RememberMe',
        'Form',
    ]
];
