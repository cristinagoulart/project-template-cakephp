<?php
namespace App\Test\TestCase\Settings;

use App\Model\Table\SettingsTable;
use App\Settings\DbConfig;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class DbConfigTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    public $Settings;

    public $configure;

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
        $this->configure = new DbConfig();
    }

    public function testGetArray()
    {
        $array = $this->configure->read('Settings');
        $this->assertInternalType('array', $array);
    }

    public function testGetEmptyArray()
    {
        $array = $this->configure->read('SettingsWrong');
        $this->assertEquals([], $array);
    }
}
