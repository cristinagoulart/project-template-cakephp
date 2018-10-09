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
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'key' => 'ScheduledLog.stats.age',
                'value' => 'Lorem ipsum dolor sit amet'
            ],
            [
                'id' => 2,
                'key' => 'Avatar.defaultImage',
                'value' => 'Lorem ipsum dolor sit amet'
            ],
        ];
        parent::init();
    }
}
