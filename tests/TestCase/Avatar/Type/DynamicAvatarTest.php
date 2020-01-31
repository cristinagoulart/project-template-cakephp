<?php

namespace App\Test\TestCase\Avatar\Type;

use App\Avatar\Type\DynamicAvatar;
use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;

class DynamicAvatarTest extends TestCase
{
    public function testGet(): void
    {
        $directory = Configure::readOrFail('Avatar.directory');

        $config = array_merge(
            Configure::readOrFail('Avatar.options.' . DynamicAvatar::class),
            ['name' => 'john', 'filename' => 'unit_tests_generated_avatar_dynamic']
        );

        $this->assertSame('/uploads/avatars/' . $config['filename'], (new DynamicAvatar($config))->get());

        unlink(WWW_ROOT . $directory . $config['filename']);
    }
}
