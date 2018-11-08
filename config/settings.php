<?php
// DB settings
return [
'Settings' => [
  'Config' => [
    'UI' => [
      'Theme' => [
        'Title' => [
          'alias' => 'ScheduledLog.stats.age',
          'links' => [
            (int)0 => 'Ldap.host'
          ],
          'type' => 'string',
          'help' => 'This is dynamic, should not be display',
          'scope' => [
            (int)0 => 'user',
            (int)1 => 'app'
          ]
        ],
        'Logo_(mini)' => [
          'alias' => 'App.jsBaseUrl',
          'links' => '',
          'type' => 'string',
          'scope' => [
            (int)0 => 'app'
          ]
        ],
        'Logo_(large)' => [
          'alias' => 'Theme.logo.large',
          'links' => '',
          'type' => 'string',
          'scope' => [
            (int)0 => 'app'
          ]
        ],
        'Ldap baseDn' => [
          'alias' => 'Ldap.baseDn',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap domain' => [
          'alias' => 'Ldap.domain',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap enabled' => [
          'alias' => 'Ldap.enabled',
          'links' => '',
          'type' => 'boolean',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap filter' => [
          'alias' => 'Ldap.filter',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap groupsFilter' => [
          'alias' => 'Ldap.groupsFilter',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap host' => [
          'alias' => 'Ldap.host',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap password' => [
          'alias' => 'Ldap.password',
          'links' => '',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap port' => [
          'alias' => 'Ldap.port',
          'links' => '',
          'type' => 'integer',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ]
      ]
    ]
  ]
]
];
