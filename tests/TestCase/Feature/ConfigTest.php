<?php

namespace App\Test\TestCase\Feature;

use App\Feature\Config;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use stdClass;

/**
 * App\Feature\Config Test Case
 */
class ConfigTest extends TestCase
{
    /**
     * @var \App\Feature\Config Config instance
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $data = [
            'name' => 'Foobar',
            'active' => false,
            'options' => [1, 'foo']
        ];
        $this->config = new Config($data);
    }

    public function tearDown()
    {
        unset($this->config);

        parent::tearDown();
    }

    public function testGetName(): void
    {
        $this->assertEquals('Foobar', $this->config->get('name'));
    }

    public function testGetActive(): void
    {
        $this->assertFalse($this->config->get('active'));
    }

    public function testGetAdditionalParameter(): void
    {
        $this->assertEquals([1, 'foo'], $this->config->get('options'));
    }

    public function testGetNonExistingParameter(): void
    {
        $this->assertNull($this->config->get('Non Existing Parameter'));
    }

    /**
     * @dataProvider requiredParametersProvider
     */
    public function testMissingRequiredParameter(string $value): void
    {
        $data = ['name' => 'Batch', 'active' => true];
        unset($data[$value]);

        $this->expectException(InvalidArgumentException::class);
        new Config($data);
    }

    /**
     * @dataProvider invalidNameProvider
     * @param mixed $value Value of name
     */
    public function testWrongParameterName($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(['name' => $value, 'active' => true]);
    }

    /**
     * @dataProvider invalidActiveProvider
     * @param mixed $value Value of active
     */
    public function testWrongParameterActive($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config(['active' => $value, 'name' => 'Batch']);
    }

    /**
     * @return mixed[]
     */
    public function requiredParametersProvider(): array
    {
        return [
            ['name'],
            ['active']
        ];
    }

    /**
     * @return mixed[]
     */
    public function invalidNameProvider(): array
    {
        return [
            [new stdClass()],
            [['array']],
            [357],
            [true],
            [null]
        ];
    }

    /**
     * @return mixed[]
     */
    public function invalidActiveProvider(): array
    {
        return [
            [new stdClass()],
            [['array']],
            ['string'],
            [1],
            [0],
            [null]
        ];
    }
}
