<?php
namespace App\Test\TestCase\Controller;

use App\Controller\SoftDeleteController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\SoftDeleteController Test Case
 */
class TrashControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.scheduled_jobs',
        'app.log_audit',
        'app.users'
    ];

    public $table;
    public $id;

    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::get('ScheduledJobs');

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->session([
            'Auth' => [
                'User' => TableRegistry::get('Users')->get($userId)->toArray()
            ]
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    public function testDelete()
    {
        $toDel = $this->table->find()->first();
        $id = $toDel->id;
        TableRegistry::get('ScheduledJobs')->delete($toDel);

        $this->get('/trash/delete/ScheduledJobs/' . $id);
        TableRegistry::get('ScheduledJobs')->addBehavior('Muffin/Trash.Trash');

        $this->assertSession('The record is permanently delete', 'Flash.flash.0.message');

        $this->expectException('Cake\Datasource\Exception\RecordNotFoundException');
        $inTrash = TableRegistry::get('ScheduledJobs')->find('onlyTrashed')->where(['id' => $id])->firstOrFail();
    }

    public function testRestore()
    {
        $toRestore = $this->table->find()->first();
        $id = $toRestore->id;

        TableRegistry::get('ScheduledJobs')->delete($toRestore);

        $this->get('/trash/restore/ScheduledJobs/' . $id);

        $this->assertSession('The record is restored', 'Flash.flash.0.message');

        $restoreData = TableRegistry::get('ScheduledJobs')->find('all')->where(['id' => $id])->firstOrFail();
        $this->assertEquals($restoreData->id, $id);
    }
}
