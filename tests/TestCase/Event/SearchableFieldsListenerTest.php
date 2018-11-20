<?php

namespace App\Test\TestCase\Event;

use App\Event\Plugin\Search\Model\SearchableFieldsListener;
use App\Test\Fixture\ThingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SearchableFieldsListenerTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Cake\ORM\Table
     */
    public $Things;

    /**
     * @var \App\Model\Table\UsersTable Table instance for Users
     */
    private $Users;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.things',
        'app.users',
    ];

    public function setUp()
    {
        parent::setUp();

        $config = TableRegistry::exists('Things') ? [] : ['className' => ThingsTable::class];
        $this->Things = TableRegistry::get('Things', $config);
        $this->Users = TableRegistry::get('Users');
    }

    public function testGetSearchableFieldsForModule()
    {
        $searchableFields = SearchableFieldsListener::getSearchableFieldsByTable(
            $this->Things,
            $this->Users->find('all')->firstOrFail()->toArray()
        );
        $this->assertCount(1, $searchableFields);
        $this->assertEquals('string', $searchableFields['Things.searchable']['type']);
    }
}
