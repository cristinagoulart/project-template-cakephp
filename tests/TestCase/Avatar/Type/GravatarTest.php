<?php

namespace App\Test\TestCase\Avatar\Type;

use App\Avatar\Type\Gravatar;
use PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    /**
     * @dataProvider getProvider
     * @param mixed[] $data Uploaded file info
     * @param mixed $expected Expected result
     * @param string $msg Descriptive error message
     */
    public function testGet(array $data, $expected, string $msg): void
    {
        $object = new Gravatar($data);

        $result = $object->get();
        $this->assertEquals($result, $expected, $msg);
    }

    /**
     * @return mixed[]
     */
    public function getProvider(): array
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
            ],
        ];
    }
}
