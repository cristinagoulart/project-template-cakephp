<?php
namespace App\Test\TestCase\ScheduledJobs\Jobs;

use App\ScheduledJobs\Jobs\CakeShellJob;
use PHPUnit\Framework\TestCase;

class CakeShellJobText extends TestCase
{

    public function testRun(): void
    {
        $command = 'CakeShell::App:foobar';

        $job = new CakeShellJob($command);

        $this->assertEquals($command, $job->getCommand());
        $this->assertEquals('', $job->getArguments());

        $result = $job->run();

        $this->assertEquals(244, $result['state']);
        $this->assertNotEmpty($result['response']['stderr']);
        $this->assertEmpty($result['response']['stdout']);
        $this->assertContains('Exception: Shell class for "Foobar" could not be found.', $result['response']['stderr']);
    }
}
