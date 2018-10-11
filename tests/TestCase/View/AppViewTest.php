<?php
namespace App\Test\TestCase\View;

use App\View\AppView;
use Cake\TestSuite\TestCase;
use Cake\View\HelperRegistry;

class AppViewTest extends TestCase
{
    public $view = null;

    public function setUp()
    {
        parent::setUp();
        $this->view = new AppView();
    }

    public function testInitialize(): void
    {
        $result = $this->view->helpers();
        $this->assertTrue(is_object($result), "List of helpers is not an object");
        $this->assertTrue($result instanceof HelperRegistry, "List of helpers is not an instance of HelperRegistry");

        $result = $result->loaded();
        $this->assertTrue(is_array($result), "List of loaded helpers is not an array");
        $this->assertFalse(empty($result), "List of loaded helpers in empty");

        $requiredHelpers = [
            'Menu',
            'Form',
            'HtmlEmail',
            'SystemInfo',
        ];
        foreach ($requiredHelpers as $helper) {
            $this->assertTrue(in_array($helper, $result), "Required helper [$helper] is not loaded");
        }
    }
}
