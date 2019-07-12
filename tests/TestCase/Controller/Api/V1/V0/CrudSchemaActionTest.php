<?php
namespace App\Test\TestCase\Controller;

use App\Crud\Action\SchemaAction;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Utility\Hash;
use ReflectionClass;

/**
 * CrudSchemaActionTest class
 */
class CrudSchemaActionTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.things',
    ];

    private $schema;

    public function setUp()
    {
        parent::setUp();
        Configure::write('CsvMigrations.modules.path', TESTS . 'config/Modules/');
        $controller = new Controller(null, null, 'Things');

        $this->schema = new SchemaAction($controller);
    }

    public function testGetFieldsSchema(): void
    {
        $data = $this->invokeMethod($this->schema, 'getFields', [[]]);
        $this->assertInternalType('array', $data);

        // Check label
        $this->assertEquals('label name', $data[1]['label']);

        // Check two fields for money and metric
        $metric_amount = [
            'name' => 'testmetric_amount',
            'type' => 'metric',
            'db_type' => 'decimal'
        ];

        $metric_unit = [
            'name' => 'testmetric_unit',
            'type' => 'metric',
            'db_type' => 'string'
        ];

        $this->assertEquals($metric_amount, Hash::extract($data, '{n}[name=testmetric_amount]')[0]);
        $this->assertEquals($metric_unit, Hash::extract($data, '{n}[name=testmetric_unit]')[0]);

        $money_amount = [
            'name' => 'testmoney_amount',
            'type' => 'money',
            'db_type' => 'decimal'
        ];

        $money_unit = [
            'name' => 'testmoney_currency',
            'type' => 'money',
            'db_type' => 'string'
        ];

        $this->assertEquals($money_amount, Hash::extract($data, '{n}[name=testmoney_amount]')[0]);
        $this->assertEquals($money_unit, Hash::extract($data, '{n}[name=testmoney_currency]')[0]);

        // Test list
        $test_list = [
           'name' => 'test_list',
            'type' => 'list',
            'options' => [
                [
                    'label' => 'first',
                    'children' => [
                        [
                            'label' => 'first children',
                            'value' => 'first.first_children'
                        ],
                        [
                            'label' => 'second children',
                            'value' => 'first.second_children'
                        ]
                    ],
                    'value' => 'first'
                ],
                [
                    'label' => 'second',
                    'value' => 'second'
                ]
            ]
        ];

        $this->assertEquals($test_list, Hash::extract($data, '{n}[name=test_list]')[0]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param SchemaAction $object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param mixed[]  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(SchemaAction &$object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
