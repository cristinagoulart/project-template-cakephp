<?php
namespace App\Test\TestCase\Avatar;

use App\Avatar\Service;
use App\Avatar\Type\Gravatar;
use App\Avatar\Type\ImageSource;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    /*
    public function testImageSource()
    {
        $service = new Service(new ImageSource([]));

        $this->assertEquals('/img/user-image-160x160.png', $service->getImage());
    }

    public function testImageSourceWithOptions()
    {
        $service = new Service(new ImageSource(['src' => '/img/foo.png']));

        $this->assertEquals('/img/foo.png', $service->getImage());
    }

    public function testGravatar()
    {
        $service = new Service(new Gravatar([]));

        $this->assertEquals(
            sprintf('https://www.gravatar.com/avatar/%s?size=160&default=mm&rating=g', md5('')),
            $service->getImage()
        );
    }

    public function testGravatarWithOptions()
    {
        $options = [
            'email' => 'john.smith@company.com',
            'size' => 256,
            'default' => 'identicon',
            'rating' => 'pg'
        ];

        $service = new Service(new Gravatar($options));

        $this->assertEquals(
            sprintf(
                'https://www.gravatar.com/avatar/%s?size=%d&default=%s&rating=%s',
                md5($options['email']),
                $options['size'],
                $options['default'],
                $options['rating']
            ),
            $service->getImage()
        );
    }
    */
}
