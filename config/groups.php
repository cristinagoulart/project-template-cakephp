<?php

use Cake\Core\Configure;

$ldapConfig = Configure::read('Ldap');

// Groups plugin configuration
return [
    'Groups' => [
        'defaultGroup' => getenv('DEFAULT_GROUP'),
        'remoteGroups' => [
            'enabled' => (bool)getenv('REMOTE_GROUPS'),
            'LDAP' => $ldapConfig
        ]
    ],
];
