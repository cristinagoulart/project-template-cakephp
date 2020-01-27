<?php

namespace App\Test\TestCase\Log\Engine;

use App\Log\Engine\JsonLinesLog;
use Cake\TestSuite\TestCase;
use Psr\Log\LogLevel;

class JsonLinesLogTest extends TestCase
{
    private $filename;
    private $config = [
        'path' => TMP,
        'file' => 'tests_app',
        'levels' => LogLevel::DEBUG,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->filename = $this->config['path'] . $this->config['file'] . '.log';

        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }

        unset($this->filename);

        parent::tearDown();
    }

    public function testLog(): void
    {
        $engine = new JsonLinesLog($this->config);

        $this->assertFalse($engine->log(LogLevel::ERROR, 'error message'));

        $expectedMessage = 'debug message';
        $this->assertTrue($engine->log(LogLevel::DEBUG, $expectedMessage));

        $fileContent = json_decode((string)file_get_contents($this->filename), true);
        $this->assertSame($expectedMessage, $fileContent['message']);
        $this->assertSame(ucfirst(LogLevel::DEBUG), $fileContent['level']);
    }

    public function testLogWithCustomMask(): void
    {
        $this->config['mask'] = 0664;
        $engine = new JsonLinesLog($this->config);

        $expectedMessage = 'debug message';
        $this->assertTrue($engine->log(LogLevel::DEBUG, $expectedMessage));

        $fileContent = json_decode((string)file_get_contents($this->filename), true);
        $this->assertSame($expectedMessage, $fileContent['message']);
        $this->assertSame(ucfirst(LogLevel::DEBUG), $fileContent['level']);
    }
}
