<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class AuditStashShellTest extends ConsoleIntegrationTestCase
{
    public $fixtures = ['app.log_audit'];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('LogAudit');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testAddUserId(): void
    {
        $expected = '00000000-0000-0000-0000-000000000001';

        $logAudit = $this->table->newEntity([
            'meta' => json_encode(['user' => $expected]),
            'timestamp' => new \DateTimeImmutable(),
            'primary_key' => '00000000-0000-0000-0000-000000000001',
            'source' => 'things',
        ]);

        $this->table->saveOrFail($logAudit);

        $logAudit = $this->table->get($logAudit->get('id'));
        $this->assertNull($logAudit->get('user_id'));

        $this->exec('audit_stash add_user_id');
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $logAudit = $this->table->get($logAudit->get('id'));
        $this->assertSame($expected, $logAudit->get('user_id'));
    }
}
