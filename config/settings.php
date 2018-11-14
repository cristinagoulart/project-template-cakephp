<?php
// DB settings
return [
  'Settings' => [
    'Config' => [
      'UI' => [
        'Theme' => [
          'Title' => [
            'alias' => 'ScheduledLog.stats.age',
            'type' => 'string',
            'help' => 'This is dynamic, should not be display',
            'scope' => ['user', 'app'],
          ],
          'Logo_(mini)' => [
            'alias' => 'App.jsBaseUrl',
            'type' => 'string',
            'scope' => ['app'],
          ],
          'Logo_(large)' => [
            'alias' => 'Theme.logo.large',
            'type' => 'string',
            'scope' => ['app'],
          ],
        ],
        'Other options' => [
          'Skin' => [
            'alias' => 'Theme.skin',
            'type' => 'string',
            'help' => 'Try red',
            'scope' => ['user', 'app'],
          ],
          'skin_url' => [
            'alias' => 'Theme.skinUrl',
            'type' => 'string',
            'help' => 'This should be same of the previous one. try "AdminLTE.skins/skin-red.min"',
            'scope' => ['user', 'app'],
          ],
          'Show_remeber' => [
            'alias' => 'Theme.login.show_remember',
            'type' => 'boolean',
            'scope' => ['user', 'app'],
          ],
          'Show_register' => [
            'alias' => 'Theme.login.show_register',
            'type' => 'boolean',
            'scope' => ['app'],
          ],
          'show_social' => [
            'alias' => 'Theme.login.show_social',
            'type' => 'boolean',
            'scope' => ['app'],
          ],
          'Folder' => [
            'alias' => 'Theme.folder',
            'type' => 'string',
            'scope' => ['app'],
          ],
          'background_image' => [
            'alias' => 'Theme.backgroundImages',
            'type' => 'string',
            'scope' => ['app'],
          ],
          'Pretend_Avatars' => [
            'alias' => 'Theme.prependAvatars',
            'type' => 'boolean',
            'scope' => ['app'],
          ]
        ]
      ]
    ],
     'Second Tab' => [
       'Column 1' => [
         'This section 1' => [
           'name1' => [
             'alias' => 'API.auth',
             'type' => 'boolean',
             'scope' => ['app'],
           ],
         ],
       ],
       'Column 2' => [
         'This section 2' => [
           'name1' => [
             'alias' => 'SystemInfo.tabs.project.label',
             'type' => 'string',
             'scope' => ['app'],
           ],
           'name2' => [
             'alias' => 'SystemInfo.tabs.project.icon',
             'type' => 'string',
             'scope' => ['app'],
           ],
         ],
       ],
     ],
     'N Tab' => [
       'Another Column' => [
         'This section 1' => [
           'name2' => [
             'alias' => 'FileStorage.defaultImageSize',
             'type' => 'string',
             'scope' => ['app'],
           ],
         ],
       ],
     ],
   ]
 ];
