<?php
// DB settings
return [
  'Settings' => [
    'First Tab' => [
       'zero line' => [
         'First Section' => [
           'field_number_one' => [
             'alias' => 'ScheduledLog.stats.age',
             'type' => 'string',
             'help' => 'This is the help 1',
           ],
           'field_number_two' => [
             'alias' => 'CsvMigrations.default_icon',
             'type' => 'string',
             'help' => 'This is the help 2',
           ],
           'field_number_three' => [
             'alias' => 'CsvMigrations.BootstrapFileInput.initialPreviewConfig.url',
             'type' => 'text',
             'help' => 'This is the help 3',
           ],
         ],
         'Second Section' => [
           'field_number_one' => [
             'alias' => 'Menu.allControllers',
             'type' => 'boolean',
             'help' => 'This is even more helpful',
           ],
         ],
       ],
       'first line' => [
         'first Section' => [
           'i_need_more_coffee' => [
             'alias' => 'CsvMigrations.tableValidation',
             'type' => 'integer',
           ],
         ],
       ],
       'second line' => [
         'secondsection' => [
           'my_birthday' => [
             'alias' => 'EmailTransport.default.host',
             'type' => 'string',
           ],
           'more_rock' => [
             'alias' => 'App.wwwRoot',
             'type' => 'string',
           ],
         ],
       ],
       'third line' => [
         'secondsection' => [
           'i_want_to_write_more' => [
             'alias' => 'Icons.url',
             'type' => 'text',
           ],
           'the_answer_is_42' => [
             'alias' => 'Whoops.editor',
             'type' => 'boolean',
           ],
         ],
       ],
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
