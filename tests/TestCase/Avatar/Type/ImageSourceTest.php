<?php

namespace App\Test\TestCase\Avatar\Type;

use App\Avatar\Type\ImageSource;
use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class ImageSourceTest extends TestCase
{
    public function testGet(): void
    {
        $this->assertSame('', (new ImageSource(['filename' => 'foo']))->get());
        $this->assertSame('bar', (new ImageSource(['filename' => 'foo', 'src' => 'bar']))->get());

        $directory = Configure::readOrFail('Avatar.directory');
        $filename = 'unit_tests_generated_avatar_image_source';
        $fh = fopen(WWW_ROOT . $directory . $filename, 'w');
        Assert::resource($fh);
        fclose($fh);

        $this->assertSame($directory . $filename, (new ImageSource(['filename' => $filename]))->get());

        unlink(WWW_ROOT . $directory . $filename);
    }
}
