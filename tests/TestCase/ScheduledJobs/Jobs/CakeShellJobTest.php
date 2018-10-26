<?php
namespace App\Test\TestCase\ScheduledJobs\Jobs;

use App\ScheduledJobs\Jobs\CakeShellJob;
use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;

class CakeShellJobText extends TestCase
{
    public function testRunMissingCommand(): void
    {
        // Try to run `./bin/cake foobar`
        $command = 'CakeShell::App:foobar';

        $job = new CakeShellJob($command);

        $this->assertEquals($command, $job->getCommand());
        $this->assertEquals('', $job->getArguments());

        $result = $job->run();

        $this->assertEquals(1, $result['state'], 'Unexpected exit code returned for non-existing command');
        $this->assertNotEmpty($result['response']['stderr']);
        $this->assertEmpty($result['response']['stdout']);
        $this->assertContains('Exception: Unknown command', $result['response']['stderr']);
    }

    public function testRunSuccessful(): void
    {
        // Try to run `./bin/cake version`
        $command = 'CakeShell::App:version';

        $job = new CakeShellJob($command);

        $this->assertEquals($command, $job->getCommand());
        $this->assertEquals('', $job->getArguments());

        $result = $job->run();

        $this->assertEquals(0, $result['state'], 'Unexpected exit code returned for a valid command');
        $this->assertEmpty($result['response']['stderr']);
        $this->assertNotEmpty($result['response']['stdout']);
        $this->assertContains(Configure::version(), $result['response']['stdout'], 'CakePHP version is missing from the STDOUT');
    }
}
