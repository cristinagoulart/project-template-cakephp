<?php

namespace App\Test\TestCase\Search;

use App\Search\MagicValue;
use Cake\TestSuite\TestCase;

/**
 * Search\Utility\MagicValue Test Case
 *
 * @property array $user
 */
class MagicValueTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = ['id' => '00000000-0000-0000-0000-000000000002'];
    }

    public function tearDown()
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(MagicValue::class, new MagicValue('foo', $this->user));
    }

    public function testConstructorExceptionInvalidUserInfo(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MagicValue('foo', []);
    }

    public function testGetWithoutMagicValue(): void
    {
        $this->assertEquals('%%foo%%', (new MagicValue('%%foo%%', $this->user))->get());
    }

    public function testGetWithMagicValue(): void
    {
        $this->assertSame('00000000-0000-0000-0000-000000000002', (new MagicValue('%%me%%', $this->user))->get());
        $this->assertEquals(
            date('Y-m-d'),
            (new MagicValue('%%today%%', $this->user))->get()
        );
        $this->assertEquals(
            date('Y-m-d', time() - (24 * 60 * 60)),
            (new MagicValue('%%yesterday%%', $this->user))->get()
        );
        $this->assertEquals(
            date('Y-m-d', time() + (24 * 60 * 60)),
            (new MagicValue('%%tomorrow%%', $this->user))->get()
        );
    }

    /**
     * @dataProvider validMagicValues
     */
    public function testShouldAcceptValidMagicValue(string $value): void
    {
        $this->assertTrue(MagicValue::is($value));
    }

    public function testShouldRequireValidMagicValue(): void
    {
        $this->assertFalse(MagicValue::is('%%foobar%%'));
    }

    /**
     * @return string[][]
     */
    public function validMagicValues(): array
    {
        return [
            ['%%me%%'],
            ['%%today%%'],
            ['%%yesterday%%'],
            ['%%tomorrow%%'],
        ];
    }
}
