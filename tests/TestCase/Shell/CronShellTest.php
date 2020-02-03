<?php

namespace App\Test\TestCase\Shell;

use App\Shell\CronShell;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

/**
 * App\Shell\CronShell Test Case
 */
class CronShellTest extends ConsoleIntegrationTestCase
{

    public $fixtures = [
        'app.log_audit',
        'app.users',
        'app.scheduled_jobs',
        'app.scheduled_job_logs',
    ];

    /**
     * Test subject
     *
     * @var \App\Shell\CronShell
     */
    public $CronShell;

    private $table;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        /** @var \Cake\Console\ConsoleIo */
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->CronShell = new CronShell($io);
        $this->table = TableRegistry::getTableLocator()->get('ScheduledJobs');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table);
        unset($this->CronShell);

        parent::tearDown();
    }

    /**
     * Test main method
     *
     * @return void
     */
    public function testMain(): void
    {
        $this->table->deleteAll([]);
        $scheduledJob = $this->table->newEntity([
            'name' => 'Test job every second',
            'job' => 'CakeShell::App:foobar',
            'recurrence' => 'FREQ=SECONDLY',
            'active' => 1,
            'priority' => 10,
            'start_date' => '2020-01-01 09:00:00',
        ]);

        $this->table->saveOrFail($scheduledJob);

        $this->exec('cron');

        $expected = [
            '<info>Running Scheduled Tasks...</info>',
            '<info>Starting Scheduled Task [Test job every second]</info>',
            '<info>Finished Scheduled Task [Test job every second]</info>',
            '<info>Logged Scheduled Task [Test job every second]</info>',
            '<info>Finished with all Schedule Tasks successfully</info>',
        ];

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains(implode(PHP_EOL, $expected));
    }

    public function testMainWithDisabledScheduledJobs(): void
    {
        Configure::write('Features.Module' . DS . 'ScheduledJobs.active', false);

        $this->exec('cron');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertErrorContains('Scheduled Tasks are disabled.  Nothing to do.');
    }

    public function testMainWithoutJobs(): void
    {
        $this->table->deleteAll([]);

        $this->exec('cron');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('No active Scheduled Tasks found.  Nothing to do.');
    }

    /**
     * @dataProvider fileAndClassNamesProvider
     * @param string $file File name
     * @param mixed $class Class name
     * @param string $normalized Normalized name
     * @return void
     */
    public function testLock(string $file, $class, string $normalized): void
    {
        $this->exec(sprintf('cron lock %s %s', $file, $class));
        $this->assertExitCode(Shell::CODE_ERROR);

        $expected = sprintf('%s%s_%s.lock.lock', sys_get_temp_dir() . DS, $normalized, md5($file));
        $this->assertTrue(file_exists($expected));
    }

    /**
     * @return mixed[]
     */
    public function fileAndClassNamesProvider(): array
    {
        return [
            ['foo', 'bar', 'bar'],
            ['foo1', 'bar1', 'bar1'],
            ['foo_1234', 'bar__1', 'bar__1'],
            ['foo\1234', 'b*a@r!5', 'b_a_r_5'],
            ['foos', null, ''],
            ['foos', '', ''],
            ['foos', '1', '1'],
        ];
    }
}
