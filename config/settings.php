<?php
// DB settings
return [
    'Settings' => [
        'Communication' => [
            'Email' => [
                'General' => [
                    'Transport' => [
                        'alias' => 'EmailTransport.default.className',
                        'type' => 'string',
                        'help' => 'Valid options are Mail, Smtp and Debug',
                        'scope' => [
                            'app',
                        ],
                    ],
                ],
                'SMTP' => [
                    'Host' => [
                        'alias' => 'EmailTransport.default.host',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Port' => [
                        'alias' => 'EmailTransport.default.port',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Timeout' => [
                        'alias' => 'EmailTransport.default.timeout',
                        'type' => 'integer',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Username' => [
                        'alias' => 'EmailTransport.default.username',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Password' => [
                        'alias' => 'EmailTransport.default.password',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'TLS' => [
                        'alias' => 'EmailTransport.default.tls',
                        'type' => 'boolean',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                ],
            ]
        ],
        'Security' => [
            'Authentication' => [
                'LDAP' => [
                    'Enabled' => [
                        'alias' => 'Ldap.enabled',
                        'type' => 'boolean',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Username' => [
                        'alias' => 'Ldap.username',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Password' => [
                        'alias' => 'Ldap.password',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Host' => [
                        'alias' => 'Ldap.host',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Port' => [
                        'alias' => 'Ldap.port',
                        'type' => 'integer',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Version' => [
                        'alias' => 'Ldap.version',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Domain' => [
                        'alias' => 'Ldap.domain',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'BaseDN' => [
                        'alias' => 'Ldap.baseDn',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                    'Filter' => [
                        'alias' => 'Ldap.filter',
                        'type' => 'string',
                        'help' => '',
                        'scope' => [
                            'app'
                        ],
                    ],
                ],
            ],
        ],
        'Other' => [
            'Development' => [
                'Troubleshooting' => [
                    'Debug' => [
                        'alias' => 'debug',
                        'type' => 'boolean',
                        'help' => 'Use this with caution! All errors will be displayed and debug messages will be logged.',
                        'scope' => [
                            'app'
                        ],
                    ],
                ],
            ],
        ],
    ],
];
