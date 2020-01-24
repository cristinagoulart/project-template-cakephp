<?php

namespace App\Test\TestCase\Log\Engine;

use App\Log\Engine\LevelAwareDatabaseLog;
use Cake\Database\Log\LoggedQuery;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Psr\Log\LogLevel;

class LevelAwareDatabaseLogTest extends TestCase
{
    public $fixtures = ['plugin.DatabaseLog.database_logs'];

    public function testLog(): void
    {
        $table = TableRegistry::getTableLocator()->get('DatabaseLogs');
        $table->deleteAll([]);

        $engine = new LevelAwareDatabaseLog(['levels' => LogLevel::DEBUG]);

        $this->assertFalse($engine->log(LogLevel::DEBUG, new LoggedQuery()), "log() did not skip logging of database queries");
        $this->assertFalse($engine->log('bad_log_level', 'test message'), "log() did not skip logging of invalid log level");

        $message = 'test message';
        $this->assertTrue($engine->log(LogLevel::DEBUG, $message));

        $query = $table->find()->all();

        $this->assertCount(1, $query);
        $this->assertSame($message, $query->first()->get('message'));
        $this->assertSame(LogLevel::DEBUG, $query->first()->get('type'));
    }
}
