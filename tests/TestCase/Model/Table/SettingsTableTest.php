<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    public $Settings;

    public $configSettings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.settings'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Settings') ? [] : ['className' => SettingsTable::class];
        $this->Settings = TableRegistry::get('Settings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Settings);

        parent::tearDown();
    }

    public function testGetAlias()
    {
        $configSettings = [
            'SchedulerLogAge' => [
                'default' => '1',
                'alias' => 'ScheduledLog.stats.age'
            ],
            'Avatar' => [
                'default' => '1',
                'alias' => 'Avatar.defaultImage'
            ]
        ];

        $this->assertEmpty($this->Settings->getAliasDiff($configSettings));
    }

    public function testGetNewRecord()
    {
        $configSettings = [
            'SchedulerLogAge' => [
                'default' => '1',
                'alias' => 'ScheduledLog.stats.age'
            ],
            'FileStorage' => [
                'default' => '1',
                'alias' => 'FileStorage.defaultImageSize'
            ],
            'Avatar' => [
                'default' => '1',
                'alias' => 'Avatar.defaultImage'
            ]
        ];

        $this->assertEquals(['FileStorage.defaultImageSize'], $this->Settings->getAliasDiff($configSettings));
    }

    public function testGetException()
    {
        $configSettings = [
            'Pizza' => [
                'default' => '1',
                'alias' => 'Pizza.With.Pinapple'
            ],
            'Avatar' => [
                'default' => '1',
                'alias' => 'Avatar.defaultImage'
            ]
        ];

        $this->expectException('\Exception');
        $this->Settings->getAliasDiff($configSettings);
    }
}
