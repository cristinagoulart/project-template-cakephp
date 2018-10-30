<?php
// DB settings
return [
  'Settings' => [
    'Config' => [
      'UI' => [
        'Theme' => [
          'Title' => [
            'alias' => 'Theme.title',
            'type' => 'string',
            'help' => 'This is dynamic, should not be display',
            'roles' => ['Everyone', 'settings', 'anotherRole'],
          ],
          'Logo_(mini)' => [
            'alias' => 'Theme.logo.mini',
            'type' => 'string',
          ],
          'Logo_(large)' => [
            'alias' => 'Theme.logo.large',
            'type' => 'string',
          ],
        ],
        'Other options' => [
          'Skin' => [
            'alias' => 'Theme.skin',
            'type' => 'string',
            'help' => 'Try red',
            'roles' => ['Everyone', 'settings'],
          ],
          'skin_url' => [
            'alias' => 'Theme.skinUrl',
            'type' => 'string',
            'help' => 'This should be same of the previous one. try "AdminLTE.skins/skin-red.min"',
            'roles' => ['Everyone', 'settings'],
          ],
          'Show_remeber' => [
            'alias' => 'Theme.login.show_remember',
            'type' => 'boolean',
          ],
          'Show_register' => [
            'alias' => 'Theme.login.show_register',
            'type' => 'boolean',
          ],
          'show_social' => [
            'alias' => 'Theme.login.show_social',
            'type' => 'boolean',
          ],
          'Folder' => [
            'alias' => 'Theme.folder',
            'type' => 'string',
          ],
          'background_image' => [
            'alias' => 'Theme.backgroundImages',
            'type' => 'string',
          ],
          'Pretend_Avatars' => [
            'alias' => 'Theme.prependAvatars',
            'type' => 'boolean',
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
           ],
         ],
       ],
       'Column 2' => [
         'This section 2' => [
           'name1' => [
             'alias' => 'SystemInfo.tabs.project.label',
             'type' => 'string',
           ],
           'name2' => [
             'alias' => 'SystemInfo.tabs.project.icon',
             'type' => 'string',
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
           ],
         ],
       ],
     ],
   ]
 ];
