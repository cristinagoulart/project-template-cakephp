<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 *
 */
class SettingsFixture extends TestFixture
{
    public $import = ['table' => 'settings'];

    /**
     * Init metho
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'key' => 'FileStorage.defaultImageSize',
                'value' => '5000',
                'scope' => 'app',
                'context' => 'app'
            ],
            [
                'id' => 2,
                'key' => 'ScheduledLog.stats.age',
                'value' => 'my value',
                'scope' => 'user',
                'context' => 'bb697cd7-c869-491d-8696-805b1af8c08f',
            ]
        ];
        parent::init();
    }
}
