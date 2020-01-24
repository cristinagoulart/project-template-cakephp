<?php

namespace App\Test\TestCase\Log\Engine;

use App\Log\Engine\LevelAwareDatabaseLog;
use Cake\Database\Log\LoggedQuery;
use Cake\TestSuite\TestCase;
use Psr\Log\LogLevel;

class LevelAwareDatabaseLogTest extends TestCase
{
    public function testLog(): void
    {
        $engine = new LevelAwareDatabaseLog(['levels' => LogLevel::DEBUG]);

        $this->assertEquals(false, $engine->log(LogLevel::DEBUG, new LoggedQuery()), "log() did not skip logging of database queries");

        $this->assertEquals(false, $engine->log('bad_log_level', 'test message'), "log() did not skip logging of invalid log level");
    }
}
