<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LogAuditTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use DateTime;

/**
 * App\Model\Table\LogAuditTable Test Case
 */
class LogAuditTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Cake\ORM\Table
     */
    public $LogAudit;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.log_audit',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('LogAudit') ? [] : ['className' => 'App\Model\Table\LogAuditTable'];
        $this->LogAudit = TableRegistry::get('LogAudit', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->LogAudit);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertInstanceOf(LogAuditTable::class, $this->LogAudit);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $result = $this->LogAudit->validationDefault($validator);

        $this->assertInstanceOf(Validator::class, $result);

        $entity = $this->LogAudit->newEntity([
            'timestamp' => new Datetime(),
            'primary_key' => '00000000-0000-0000-0000-000000001234',
            'source' => 'Foo',
        ]);

        $this->assertEmpty($entity->getErrors());
    }
}
