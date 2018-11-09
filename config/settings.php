<?php
// DB settings
return [
'Settings' => [
  'first' => [
    'Col' => [
      'section' => [
        'Ldap baseDn' => [
          'alias' => 'Ldap.baseDn',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ]
      ]
    ],
    'col 2' => [
      'section' => [
        'Ldap domain' => [
          'alias' => 'Ldap.domain',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ]
      ]
    ]
  ],
  'second' => [
    'col 1' => [
      'second sec' => [
        'Ldap password' => [
          'alias' => 'Ldap.password',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ]
      ]
    ],
    'col 2' => [
      'second asdf' => [
        'Ldap username' => [
          'alias' => 'Ldap.username',
          'type' => 'string',
          'help' => '',
          'scope' => [
            (int)0 => 'app',
            (int)1 => 'user'
          ]
        ],
        'Ldap version' => [
          'alias' => 'Ldap.version',
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
