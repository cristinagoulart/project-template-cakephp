<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LogsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LogsTableTest extends TestCase
{
    public $fixtures = [
        'plugin.DatabaseLog.database_logs',
    ];

    private $table;

    public function setUp()
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Logs');
    }

    public function tearDown()
    {
        unset($this->table);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertInstanceOf(LogsTable::class, $this->table);
        $this->assertSame('database_logs', $this->table->getTable());
        $this->assertTrue($this->table->hasBehavior('Searchable'));
        $this->assertTrue($this->table->hasBehavior('Timestamp'));
    }
}
