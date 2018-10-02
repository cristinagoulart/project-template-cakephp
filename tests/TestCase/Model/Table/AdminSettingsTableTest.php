<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AdminSettingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AdminSettingsTable Test Case
 */
class AdminSettingsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\AdminSettingsTable
     */
    public $AdminSettings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.admin_settings'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('AdminSettings') ? [] : ['className' => AdminSettingsTable::class];
        $this->AdminSettings = TableRegistry::get('AdminSettings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AdminSettings);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
