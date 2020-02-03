<?php

namespace App\Test\TestCase\Avatar;

use App\Avatar\Service;
use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

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

    public function testGetImage(): void
    {
        $type = Configure::readOrFail('Avatar.default');
        $options = (array)Configure::readOrFail('Avatar.options.' . $type);

        $options = array_merge($options, [
            'id' => '123',
            'email' => 'foo@bar.com',
            'name' => 'Foobar',
        ]);

        $this->assertSame('/uploads/avatars/123.png', $this->Service->getImage($options));

        unlink(WWW_ROOT . Configure::readOrFail('Avatar.directory') . '123.png');
    }

    public function testIsImage(): void
    {
        $data = ['type' => 'image/png'];
        $this->assertTrue($this->Service->isImage($data));

        $data = ['type' => 'document/pdf'];
        $this->assertFalse($this->Service->isImage($data));
    }

    public function testIsAllowedSize(): void
    {
        $data = ['size' => 524287];
        $this->assertTrue($this->Service->isAllowedSize($data));

        $data = ['size' => 524288];
        $this->assertFalse($this->Service->isAllowedSize($data));
    }

    public function testGetImageResource(): void
    {
        $data = ['name' => 'cake-logo.png', 'tmp_name' => WWW_ROOT . 'img' . DS . 'cake-logo.png'];
        $this->assertInternalType('resource', $this->Service->getImageResource($data));

        $data = ['name' => 'cakephp.jpg', 'tmp_name' => WWW_ROOT . 'img' . DS . 'branding' . DS . 'cakephp' . DS . 'cakephp.jpg'];
        $this->assertInternalType('resource', $this->Service->getImageResource($data));

        $path = WWW_ROOT . 'img' . DS . 'cake-logo.png';
        $this->assertInternalType('resource', $this->Service->getImageResource($path, true));

        $data = ['name' => 'doc.pdf'];
        $result = $this->Service->getImageResource($data);
        Assert::boolean($result);
        $this->assertFalse($result);
    }

    public function testSaveImage(): void
    {
        $filename = 'test_save_avatar_image.png';
        $data = ['name' => 'cake-logo.png', 'tmp_name' => WWW_ROOT . 'img' . DS . 'cake-logo.png'];
        $resource = $this->Service->getImageResource($data);
        Assert::resource($resource);

        $customDir = WWW_ROOT . Configure::readOrFail('Avatar.customDirectory');

        $this->assertTrue($this->Service->saveImage($customDir . $filename, $resource));

        unlink(WWW_ROOT . Configure::readOrFail('Avatar.customDirectory') . $filename);
    }

    public function testRemoveImageResource(): void
    {
        $data = ['name' => 'cake-logo.png', 'tmp_name' => WWW_ROOT . 'img' . DS . 'cake-logo.png'];
        $resource = $this->Service->getImageResource($data);
        Assert::resource($resource);

        $this->assertTrue($this->Service->removeImageResource($resource));
    }
}
