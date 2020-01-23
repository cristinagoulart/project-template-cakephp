<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 *
 */
class SettingsFixture extends TestFixture
{
    public $import = ['model' => 'Settings'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
            [
                'id' => 1,
                'key' => 'FileStorage.defaultImageSize',
                'value' => 'huge',
                'scope' => 'app',
                'context' => 'app',
            ],
            [
                'id' => 2,
                'key' => 'ScheduledLog.stats.age',
                'value' => '-1 month',
                'scope' => 'app',
                'context' => 'bb697cd7-c869-491d-8696-805b1af8c08f',
            ],
            [
                'id' => 3,
                'key' => 'dashboard_menu_order_value',
                'value' => '',
                'scope' => 'user',
                'context' => '00000000-0000-0000-0000-000000000001',
            ],
            [
                'id' => 4,
                'key' => 'dashboard_menu_order_value',
                'value' => '',
                'scope' => 'app',
                'context' => 'app',
            ],
        ];
}
