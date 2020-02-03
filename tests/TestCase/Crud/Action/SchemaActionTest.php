<?php

namespace App\Test\TestCase\Crud\Action;

use App\Crud\Action\SchemaAction;
use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

class SchemaActionTest extends TestCase
{
    public function testSchema(): void
    {
        $schema = new SchemaAction(new Controller(null, null, 'Things'));
        $data = $this->invokeMethod($schema, 'getFields', [[]]);
        $this->assertInternalType('array', $data);

        // Check label
        $this->assertEquals('label name', $data[1]['label']);

        // Check two fields for money and metric
        $expected = [
            [
                'name' => 'testmetric_amount',
                'type' => 'metric',
                'db_type' => 'decimal',
            ],
            [
                'name' => 'testmetric_unit',
                'type' => 'metric',
                'db_type' => 'string',
            ],
            [
                'name' => 'testmoney_amount',
                'type' => 'money',
                'db_type' => 'decimal',
            ],
            [
                'name' => 'testmoney_currency',
                'type' => 'money',
                'db_type' => 'string',
            ],
            [
                'name' => 'test_list',
                'type' => 'sublist',
                'db_type' => 'string',
                'options' => [
                    [
                        'label' => 'first',
                        'children' => [
                            [
                                'label' => 'first children',
                                'value' => 'first.first_children',
                            ],
                            [
                                'label' => 'second children',
                                'value' => 'first.second_children',
                            ],
                        ],
                        'value' => 'first',
                    ],
                    [
                        'label' => 'second',
                        'value' => 'second',
                    ],
                ],
            ],
        ];

        $this->assertSame([], array_diff(Hash::flatten($expected), Hash::flatten($data)));
    }

    /**
     * Call protected/private method of a class.
     *
     * @param SchemaAction $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param mixed[] $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    private function invokeMethod(SchemaAction $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
