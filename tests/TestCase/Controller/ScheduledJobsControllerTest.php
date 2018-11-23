<?php
namespace App\Test\TestCase\Controller;

use App\Controller\ScheduledJobsController;
use App\Feature\Factory;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ScheduledJobsController Test Case
 */
class ScheduledJobsControllerTest extends IntegrationTestCase
{

    /**
     * @var \App\Model\Table\ScheduledJobsTable $table
     */
    private $table;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.scheduled_jobs',
        'app.scheduled_job_logs',
        'app.users',
        'app.log_audit',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        /**
         * @var \App\Model\Table\ScheduledJobsTable $table
         */
        $table = TableRegistry::getTableLocator()->get('ScheduledJobs');
        $this->table = $table;

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->session([
            'Auth' => [
                'User' => TableRegistry::get('Users')->get($userId)->toArray()
            ]
        ]);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $feature = Factory::get('Module' . DS . 'ScheduledJobs');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Scheduled jobs module is diabled');
        }

        $data = [
            'name' => 'App::Foo:bar test scheduled job',
            'start_date' => '2018-09-01 15:31:23',
        ];

        $this->post('/scheduled-jobs/add', $data);

        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $entity = $this->table->find()->where(['name' => $data['name']])->first();
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('Scheduled Job has been saved.', 'Flash.flash.0.message');
        $this->assertEquals($this->table->getStartDate($data['start_date']), $entity->get('start_date'));

        $time = Time::now();

        $data = [
            'name' => 'App::Foo:bar test scheduled job - 2',
            'start_date' => $time,
        ];

        $this->post('/scheduled-jobs/add', $data);
        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $entity = $this->table->find()->where(['name' => $data['name']])->first();
        $this->assertEquals($this->table->getStartDate($data['start_date']), $entity->get('start_date'));
    }

    public function testEdit(): void
    {
        $feature = Factory::get('Module' . DS . 'ScheduledJobs');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Scheduled jobs module is diabled');
        }

        $id = '00000000-0000-0000-0000-000000000002';

        $data = [
            'name' => 'Foo Job',
            'start_date' => '2018-09-01 15:31:23',
        ];

        $this->post('/scheduled-jobs/edit/' . $id, $data);

        /**
         * @var \Cake\Datasource\EntityInterface
         */
        $entity = $this->table->find()->where(['name' => $id])->first();

        $this->assertRedirect(['action' => 'view', $id]);
        $this->assertSession('The record has been saved.', 'Flash.flash.0.message');

        $entity = $this->table->get($id);
        $this->assertEquals($entity->get('name'), $data['name']);
        $this->assertEquals($this->table->getStartDate($data['start_date']), $entity->get('start_date'));
    }
}
