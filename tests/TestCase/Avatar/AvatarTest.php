<?php
namespace App\Test\TestCase\Avatar;

use App\Avatar\Service;
use App\Avatar\Type\Gravatar;
use App\Avatar\Type\ImageSource;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testImageSource()
    {
        $service = new Service(new ImageSource(['src' => '']));
        $options = [
            'id' => '1',
            'email' => 'foo@example.com',
        ];

        $this->assertEquals('/uploads/avatars/1.png', $service->getImage($options));
    }

    public function testImageSourceWithOptions()
    {
        $service = new Service(new ImageSource([]));
        $options = [
            'id' => '1',
            'email' => 'foo@example.com',
        ];

        $this->assertEquals('/uploads/avatars/1.png', $service->getImage($options));
    }
}
