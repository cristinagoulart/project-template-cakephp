<?php

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\SystemInfoHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class SystemInfoHelperTest extends TestCase
{
    public $helper = null;

    public function setUp()
    {
        parent::setUp();
        $View = new View();
        $this->helper = new SystemInfoHelper($View);
    }

    public function testGetProjectVersion(): void
    {
        $result = $this->helper->getProjectVersion();
        $this->assertTrue(is_string($result), "Returned version is not a string");
        $this->assertFalse(empty($result), "Returned version is an empty string");
    }

    public function testGetProjectUrl(): void
    {
        $result = $this->helper->getProjectUrl();
        $this->assertTrue(is_string($result), "Returned URL is not a string");
        $this->assertFalse(empty($result), "Returned URL is an empty string");
    }

    public function testGetProjectName(): void
    {
        $result = $this->helper->getProjectName();
        $this->assertTrue(is_string($result), "Returned name is not a string");
        $this->assertFalse(empty($result), "Returned name is an empty string");
    }

    public function testGetProgressValue(): void
    {
        $result = $this->helper->getProgressValue(0, 0);
        $this->assertEquals('0%', $result, "Incorrect progress for zero values");

        $result = $this->helper->getProgressValue(20, 100);
        $this->assertEquals('20%', $result, "Incorrect progress for integer values");

        $result = $this->helper->getProgressValue(1.25, 100);
        $this->assertEquals('1%', $result, "Incorrect progress for float values");
    }

    public function testGetProjectLogo(): void
    {
        $result = $this->helper->getProjectLogo();
        $this->assertTrue(is_string($result), "Returned project logo is not a string");
        $this->assertFalse(empty($result), "Returned project logo is an empty string");
    }

    public function testGetCopyright(): void
    {
        $result = $this->helper->getCopyright();
        $this->assertTrue(is_string($result), "Returned project logo is not a string");
        $this->assertFalse(empty($result), "Returned project logo is an empty string");
    }
}
