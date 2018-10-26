<?php
namespace App\Test\TestCase\SystemInfo;

use App\SystemInfo\Cake;
use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;

class CakeTest extends TestCase
{
    public function testGetVersion(): void
    {
        $result = Cake::getVersion();
        $expected = Configure::version();
        $this->assertEquals($expected, $result, "getVersion() returned wrong version");
    }

    public function testGetVersionUrl(): void
    {
        // When no version is specified, the URL should end with semantic version number
        $result = Cake::getVersionUrl();
        $this->assertRegexp('/\d+\.\d+.\d+$/', $result, "getVersionUrl() returned wrong URL for non-specified version");

        // When version is specified, the URL should end with the given version number
        $result = Cake::getVersionUrl('foobar');
        $this->assertEquals('https://github.com/cakephp/cakephp/releases/tag/foobar', $result, "getVersionUrl() returned wrong URL for specific version");
    }

    public function testGetLoadedPlugins(): void
    {
        $result = Cake::getLoadedPlugins();
        $this->assertTrue(is_array($result), "getLoadedPlugins() returned a non-array result");
        $this->assertFalse(empty($result), "getLoadedPlugins() returned an empty result");
    }
}
