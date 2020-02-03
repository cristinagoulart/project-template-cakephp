<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class ScheduledLogShellTest extends ConsoleIntegrationTestCase
{
    public $fixtures = ['app.scheduled_job_logs'];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('ScheduledJobLogs');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testGc(): void
    {
        $this->table->deleteAll([]);

        $scheduledJobLog = $this->table->newEntity([
            'datetime' => '2019-01-01 09:00:00',
            'created' => '2019-01-01 09:00:00',
        ]);

        $this->table->saveOrFail($scheduledJobLog);

        $this->exec('scheduled_log gc');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<info>Removed 1 log records older than 1 month.</info>');
        $this->assertOutputContains('Clean up scheduled job logs older then -1 month.</info>');
    }

    public function testGcWithoutAge(): void
    {
        $this->expectException(\RuntimeException::class);

        Configure::write('ScheduledLog.stats.age', false);

        $this->exec('scheduled_log gc');
    }

    public function testGcWithCustomAge(): void
    {
        $this->table->deleteAll([]);

        $scheduledJobLog = $this->table->newEntity([
            'datetime' => new \DateTimeImmutable('-1 year'),
            'created' => new \DateTimeImmutable('-1 year'),
        ]);

        $this->table->saveOrFail($scheduledJobLog);

        $this->exec('scheduled_log gc --age="-2 years"');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<info>Removed 0 log records older than 2 years.</info>');
    }
}
