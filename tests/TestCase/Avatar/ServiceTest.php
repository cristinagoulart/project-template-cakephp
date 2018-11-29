<?php
namespace App\Test\TestCase\Avatar;

use App\Avatar\Service;
use Cake\Core\Configure;
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

    public function testDefaultAvatarSource(): void
    {
        $avatar = $this->Service->getAvatarSource();
        $default = Configure::read('Avatar.order.0');

        $this->assertInstanceOf($default, $avatar);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokeAvatarSourceWrongClass(): void
    {
        $this->Service->invokeAvatarSource('\Foo\Bar\Baz');
    }

    public function testGetImageName(): void
    {
        $result = $this->Service->getImageName(['id' => 'foobar']);
        $extension = Configure::read('Avatar.extension');

        $this->assertEquals($result, 'foobar' . $extension);
    }
}
