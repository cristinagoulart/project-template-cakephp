<?php
namespace App\Test\TestCase\Avatar\Type;

use App\Avatar\Type\Gravatar;
use PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    /**
     * @dataProvider testGetProvider
     */
    public function testGet($data, $expected, $msg)
    {
        $object = new Gravatar($data);

        $result = $object->get();
        $this->assertEquals($result, $expected, $msg);
    }

    public function testGetProvider()
    {
        return [
            [
                [
                    'filename' => '123.png',
                    'email' => 'example@example.com',
                    'id' => '123',
                    'name' => 'John Doe',
                ],
                false,
                'Non existing email should return 404 from Gravar',
            ]
        ];
    }
}
