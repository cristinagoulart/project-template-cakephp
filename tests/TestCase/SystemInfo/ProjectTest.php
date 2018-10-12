<?php
namespace App\Test\TestCase\SystemInfo;

use App\SystemInfo\Project;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testGetName(): void
    {
        $result = Project::getName();
        $this->assertTrue(is_string($result), "getName() returned a non-string result");
        $this->assertFalse(empty($result), "getName() returned an empty result");
    }

    public function testGetUrl(): void
    {
        $result = Project::getUrl();
        $this->assertTrue(is_string($result), "getUrl() returned a non-string result");
        $this->assertFalse(empty($result), "getUrl() returned an empty result");
    }

    public function testGetDisplayVersion(): void
    {
        $result = Project::getDisplayVersion();
        $this->assertTrue(is_string($result), "getDisplayVersion() returned a non-string result");
        $this->assertFalse(empty($result), "getDisplayVersion() returned an empty result");
    }

    public function testGetBuildVersions(): void
    {
        $result = Project::getBuildVersions();
        $this->assertTrue(is_array($result), "getBuildVersions() returned a non-array result");
        $this->assertFalse(empty($result), "getBuildVersions() returned an empty result");

        $versions = ['current', 'deployed', 'previous'];
        foreach ($versions as $version) {
            $this->assertTrue(array_key_exists($version, $result), "getBuildVersions() returned result without '$version' key");
            $this->assertTrue(is_string($result[$version]), "getBuildVersions() returned a non-string value for '$version' key");
            $this->assertFalse(empty($result[$version]), "getBuildVersions() returned an empty value for '$version' key");
        }
    }

    public function testGetLogo(): void
    {
        $result = Project::getLogo();
        $this->assertTrue(is_string($result), "getLogo() returned a non-string result");
        $this->assertFalse(empty($result), "getLogo() returned an empty result");
    }

    public function testCopyright(): void
    {
        $result = Project::getCopyright();
        $this->assertTrue(is_string($result), "getCopyright() returned a non-string result");
        $this->assertFalse(empty($result), "getCopyright() returned an empty result");
    }
}
