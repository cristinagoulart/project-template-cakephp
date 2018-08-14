<?php
namespace App\Test\TestCase\Controller;

use App\Controller\ScheduledJobsController;
use App\Feature\Factory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ScheduledJobsController Test Case
 */
class ScheduledJobsControllerTest extends IntegrationTestCase
{

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

        $this->table = TableRegistry::get('ScheduledJobs');

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
    public function testAdd()
    {
        $feature = Factory::get('Module' . DS . 'ScheduledJobs');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Scheduled jobs module is diabled');
        }

        $data = [
            'name' => 'App::Foo:bar test scheduled job'
        ];

        $this->post('/scheduled-jobs/add', $data);

        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('Scheduled Job has been saved.', 'Flash.flash.0.message');
    }

    public function testEdit()
    {
        $feature = Factory::get('Module' . DS . 'ScheduledJobs');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Scheduled jobs module is diabled');
        }

        $id = '00000000-0000-0000-0000-000000000002';

        $data = [
            'name' => 'Foo Job',
        ];

        $this->post('/scheduled-jobs/edit/' . $id, $data);

        $this->assertRedirect(['action' => 'view', $id]);
        $this->assertSession('The record has been saved.', 'Flash.flash.0.message');

        $entity = $this->table->get($id);
        $this->assertEquals($entity->get('name'), $data['name']);
    }
}
