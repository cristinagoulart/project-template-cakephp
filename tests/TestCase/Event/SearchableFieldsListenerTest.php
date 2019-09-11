<?php

namespace App\Test\TestCase\Event;

use App\Event\Plugin\Search\Model\SearchableFieldsListener;
use App\Test\Fixture\ThingsTable;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Event\EventName;

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
    protected $Users;

    /**
     * @var \App\Model\Table\ScheduledJobsTable Table instance for ScheduledJobs
     */
    protected $ScheduledJobs;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.things',
        'app.users',
        'app.scheduled_jobs',
        'plugin.Search.saved_searches',
    ];

    public function setUp()
    {
        parent::setUp();

        $config = TableRegistry::exists('Things') ? [] : ['className' => ThingsTable::class];
        $this->Things = TableRegistry::get('Things', $config);
        /**
         * @var \App\Model\Table\UsersTable $usersTable
         */
        $usersTable = TableRegistry::get('Users');
        $this->Users = $usersTable;

        /**
         * @var \App\Model\Table\ScheduledJobsTable $scheduledJobs
         */
        $scheduledJobs = TableRegistry::get('ScheduledJobs');
        $this->ScheduledJobs = $scheduledJobs;
    }

    public function testGetSearchableFields(): void
    {
        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $query = $this->Users->find('all')
            ->enableHydration(true)
            ->firstOrFail();
        $user = $query->toArray();
        $searchableFields = SearchableFieldsListener::getSearchableFieldsByTable(
            $this->Things,
            $user,
            false
        );

        $this->assertCount(31, $searchableFields);
        $this->assertEquals('string', $searchableFields['Things.name']['type']);
    }

    public function testGetSearchableFieldsWithAssociations(): void
    {
        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $query = $this->Users->find('all')
            ->enableHydration(true)
            ->firstOrFail();
        $user = $query->toArray();

        $searchableFields = SearchableFieldsListener::getSearchableFieldsByTable(
            $this->Things,
            $user,
            true
        );

        // 31 searchable fields in Things +
        // 6 searchable fields in AssignedTo (Users) +
        // 6 searchable fields in CreatedBy (Users) +
        // 6 searchable fields in Modified (Users)
        $this->assertCount(31 + 3*6, $searchableFields);
        $this->assertEquals('string', $searchableFields['Things.name']['type']);
        $this->assertEquals('string', $searchableFields['ModifiedByUsers.modified']['type']);
    }

    public function testGetBasicSearchFieldsFromView(): void
    {
        $event = new Event(
            (string)EventName::MODEL_SEARCH_BASIC_SEARCH_FIELDS()
        );
        $listener = new SearchableFieldsListener();
        $listener->getBasicSearchFields($event, $this->ScheduledJobs);
        $this->assertCount(6, $event->getResult());
    }
}
