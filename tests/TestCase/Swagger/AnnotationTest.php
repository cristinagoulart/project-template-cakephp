<?php

namespace App\Test\TestCase\Swagger;

use App\Swagger\Annotation;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use ReflectionClass;

class AnnotationTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.dblists',
        'plugin.CsvMigrations.dblist_items'
    ];

    /**
     * @dataProvider propertyOptions
     */
    public function testGetPropertyOptions(string $fieldType, string $expectedType, string $expectedFormat): void
    {
        $field = new CsvField(['name' => 'fieldName', 'type' => $fieldType, 'limit' => 'listName']);
        $result = $this->invokeMethod(
            new Annotation('Common', ''),
            'getPropertyOptions',
            [['field' => $field]]
        );
        $this->assertEquals($expectedType, $result['type']);
        $this->assertEquals($expectedFormat, $result['format']);
    }

    /**
     * @return mixed[]
     */
    public function propertyOptions(): array
    {
        // type (input), type (output), format (output)
        $propertyOptions = [
            ['uuid', 'string', 'uuid'],
            ['related', 'string', 'uuid'],
            ['files', 'string', 'uuid'],
            ['images', 'string', 'uuid'],
            ['text', 'string', 'text'],
            ['boolean', 'boolean', 'boolean'],
            ['datetime', 'string', 'date-time'],
            ['datetime', 'string', 'date-time'],
            ['list(countries)', 'string', 'list'],
            ['sublist(countries)', 'string', 'list'],
            ['dblist(companies)', 'string', 'list'],
            ['date', 'string', 'date'],
            ['time', 'string', 'time'],
            ['decimal', 'number', 'float'],
            ['integer', 'integer', 'integer'],
            ['email', 'string', 'email'],
            ['phone', 'string', 'phone'],
            ['url', 'string', 'url'],
            ['blob', 'string', 'blob'],
            ['string', 'string', 'string'],
            ['string(255)', 'string', 'string'],
        ];

        return $propertyOptions;
    }

    /**
     * Access protected and private method
     *
     * @param  mixed $object Class to access
     * @param  string $methodName Method name
     * @param  mixed[] $parameters arguments of the methods
     * @return mixed
     */
    public function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
