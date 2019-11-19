<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ThingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class ThingsTableTest extends TestCase
{
    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Things');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testInitialize(): void
    {
        $this->assertInstanceOf(ThingsTable::class, $this->table);
        $this->assertSame('things', $this->table->getTable());
        $this->assertSame('id', $this->table->getPrimaryKey());
        $this->assertSame(true, $this->table->hasBehavior('Timestamp'));
    }
}
