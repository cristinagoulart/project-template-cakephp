<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class DatabaseLogShellTest extends ConsoleIntegrationTestCase
{
    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Logs');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testGc(): void
    {
        $this->table->deleteAll([]);

        $databaseLog = $this->table->newEntity([
            'message' => 'an info message',
            'type' => 'info',
            'created' => new \DateTimeImmutable('-1 year'),
        ]);
        $this->table->saveOrFail($databaseLog);

        $databaseLog = $this->table->get($databaseLog->get('id'));

        $this->exec('database_log gc');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<info>Removed 1 log records older than 1 month.</info>');
    }

    public function testGcWithoutMaxLength(): void
    {
        Configure::write('DatabaseLog.maxLength', false);

        $this->exec('database_log gc');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<info>Required parameter "maxLength" is not defined (garbage collector)</info>');
    }

    public function testStats(): void
    {
        $this->table->deleteAll([]);

        $databaseLog = $this->table->newEntity([
            'message' => 'an info message',
            'type' => 'info',
        ]);
        $this->table->saveOrFail($databaseLog);

        $databaseLog = $this->table->get($databaseLog->get('id'));

        $this->exec('database_log stats');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains(
            '<info>Log level: info</info>' . "\n" . '    1 : an info message' . "\n\n" . '---------------------------------------------------------------'
        );
    }
}
