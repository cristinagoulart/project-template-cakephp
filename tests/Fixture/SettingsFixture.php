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
            ]
        ];
        parent::init();
    }
}
