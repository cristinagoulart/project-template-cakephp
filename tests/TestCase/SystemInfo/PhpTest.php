<?php
namespace App\Test\TestCase\SystemInfo;

use App\SystemInfo\Php;
use PHPUnit\Framework\TestCase;

class PhpTest extends TestCase
{
    public function testGetVersion(): void
    {
        $result = Php::getVersion();
        $this->assertTrue(is_string($result), "getVersion() returned a non-string result for PHP version");
        $this->assertEquals(PHP_VERSION, $result, "getVersion() returned invalid PHP version");

        // Core extension should always be the same version as PHP itself
        $result = Php::getVersion('Core');
        $this->assertTrue(is_string($result), "getVersion() returned a non-string result for Core extension");
        $this->assertEquals(PHP_VERSION, $result, "getVersion() returned invalid Core extension version");
    }

    public function testGetSapi(): void
    {
        $result = Php::getSapi();
        $this->assertTrue(is_string($result), "getSapi() returned a non-string result");
        $this->assertFalse(empty($result), "getSapi() returned an empty result");
    }

    public function testGetLoadedExtensions(): void
    {
        $result = Php::getLoadedExtensions();
        $this->assertTrue(is_array($result), "getLoadedExtensions() returned a non-array result");
        $this->assertFalse(empty($result), "getLoadedExtensions() returned an empty result");
        $this->assertTrue(array_key_exists('Core', $result), "getLoadedExtensions() is missing Core extension");
        $this->assertEquals(PHP_VERSION, $result['Core'], "getLoadedExtensions() returned invalid version for Core extension");
    }

    public function testGetUser(): void
    {
        $result = Php::getUser();
        $this->assertTrue(is_string($result), "getUser() returned a non-string result");
        $this->assertEquals(get_current_user(), $result, "getUser() returned an incorrect result");
    }

    public function testGetBinary(): void
    {
        $result = Php::getBinary();
        $this->assertTrue(is_string($result), "getBinary() returned a non-string result");
        $this->assertEquals(PHP_BINARY, $result, "getBinary() returned an incorrect result");
    }

    public function testGetIniPath(): void
    {
        $result = Php::getIniPath();
        $this->assertTrue(is_string($result), "getIniPath() returned a non-string result");
        $this->assertEquals(php_ini_loaded_file(), $result, "getIniPath() returned an incorrect result");
    }

    public function testGetIniValue(): void
    {
        $result = Php::getIniValue('memory_limit');
        $this->assertEquals(ini_get('memory_limit'), $result, "getIniValue() returned an incorrect result");
    }

    public function testGetMemoryLimit(): void
    {
        $result = Php::getMemoryLimit();
        $this->assertTrue(is_int($result), "getMemoryLimit() returned a non-integer result");
    }

    public function testGetMaxExecutionTime(): void
    {
        $result = Php::getMaxExecutionTime();
        $this->assertTrue(is_numeric($result), "getMaxExecutionTime() returned a non-numeric result");
    }

    public function testGetUploadMaxFilesize(): void
    {
        $result = Php::getUploadMaxFilesize();
        $this->assertTrue(is_int($result), "getUploadMaxFilesize() returned a non-integer result");
    }
    public function testGetPostMaxSize(): void
    {
        $result = Php::getPostMaxSize();
        $this->assertTrue(is_int($result), "getPostMaxSize() returned a non-integer result");
    }
}
