<?php
namespace App\Test\TestCase\Avatar;

use App\Avatar\Service;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @var \App\Avatar\Service $Service
     */
    public $Service;

    public function setUp()
    {
        $this->Service = new Service();
    }

    public function tearDown()
    {
        unset($this->Service);
    }

    public function testDefaultAvatarSource()
    {
        $avatar = $this->Service->getAvatarSource();
        $default = Configure::read('Avatar.order.0');

        $this->assertInstanceOf($default, $avatar);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokeAvatarSourceWrongClass()
    {
        $this->Service->invokeAvatarSource('\Foo\Bar\Baz');
    }

    public function testGetImageName()
    {
        $result = $this->Service->getImageName(['id' => 'foobar']);
        $extension = Configure::read('Avatar.extension');

        $this->assertEquals($result, 'foobar' . $extension);
    }
}
