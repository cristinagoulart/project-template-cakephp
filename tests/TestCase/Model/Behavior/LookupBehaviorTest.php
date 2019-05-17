<?php
namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\LookupBehavior;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Behavior\LookupBehavior Test Case
 */
class LookupBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Behavior\LookupBehavior
     */
    public $Lookup;

    public $table;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.things'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->table = TableRegistry::get('Things');

        $config = [
            'lookupFields' => [
                'name'
            ]
        ];

        $this->Lookup = new LookupBehavior($this->table, $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        unset($this->Lookup);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testBeforeFind() : void
    {
        $entity = $this->table->find();
        $event = new Event('Model.beforeFind', $this->table, [
            'entity' => $entity,
        ]);

        $options = new ArrayObject([
            'lookup' => true,
            'value' => 'Thing #2'
        ]);

        $primary = true;
        $this->Lookup->beforeFind($event, $entity, $options, $primary);
        $idResult = $entity->firstOrFail()->get('id');

        $this->assertEquals('00000000-0000-0000-0000-000000000002', $idResult);
    }
}
