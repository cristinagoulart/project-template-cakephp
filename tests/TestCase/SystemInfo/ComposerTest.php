<?php

namespace App\Test\TestCase\SystemInfo;

use App\SystemInfo\Composer;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testGetInstalledPackages(): void
    {
        $result = Composer::getInstalledPackages();
        $this->assertTrue(is_array($result), "getInstalledPackages() returned a non-array result");
        $this->assertFalse(empty($result), "getInstalledPackages() returned an empty result");
    }

    public function testGetMatchCounts(): void
    {
        $packages = [
            ['name' => 'package1', 'description' => 'foo'],
            ['name' => 'package2', 'description' => 'blah'],
        ];

        $matchWords = ['blah', 'package'];
        $result = Composer::getMatchCounts($packages, $matchWords);
        $this->assertTrue(is_array($result), "getMatchCounts() returned a non-array result");
        $this->assertFalse(empty($result), "getMatchCounts() returned an result");
        $this->assertTrue(array_key_exists('blah', $result), "getMatchCounts() failed to match word");
        $this->assertEquals(1, $result['blah'], "getMatchCounts() returned invalid match count for 'blah'");
        $this->assertTrue(array_key_exists('package', $result), "getMatchCounts() failed to match word");
        $this->assertEquals(2, $result['package'], "getMatchCounts() returned invalid match count for 'package");
    }
}
