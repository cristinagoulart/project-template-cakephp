<?php
namespace App\Test\TestCase\Controller;

use App\Feature\Factory;
use App\Model\Entity\Thing;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Test\App\Controller\ThingsController Test Case
 */
class ThingsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.things',
        'app.users',
        'app.log_audit',
        'app.file_storage',
        'plugin.Menu.menus',
        'plugin.Menu.menu_items',
        'plugin.RolesCapabilities.roles',
    ];

    public function setUp() : void
    {
        parent::setUp();

        $this->enableRetainFlashMessages();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->session([
            'Auth' => [
                'User' => TableRegistry::get('Users')->get($userId)->toArray()
            ]
        ]);
    }

    public function testViewUnauthenticatedFails() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/view/00000000-0000-0000-0000-000000000001');
        $this->assertRedirectContains('/login');
    }

    public function testView() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->get('/things/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
    }

    public function testAddUnauthenticatedFails() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/add');

        $this->assertRedirectContains('/login');
    }

    public function testAdd() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->get('/things/add');
        $this->assertResponseOk();
        // form element and attributes
        $this->assertResponseContains('<form');
        $this->assertResponseContains('action="/things/add"');
        // submit button
        $this->assertResponseContains('type="submit"');
        // input element(s) and attributes
        $this->assertResponseContains('name');
        $this->assertResponseContains('name="Things[name]"');
    }

    public function testAddPostData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $data = [
            'type' => 'a',
            'name' => 'test',
            'country' => 'CY',
            'gender' => 'm',
            'email' => 'name@domain.com'
        ];

        $this->post('/things/add', $data);
        $this->assertResponseSuccess();

        // fetch new record
        $query = TableRegistry::get('Things')->find()->where(['name' => $data['name']]);

        $this->assertEquals(1, $query->count());
    }

    public function testEditUnauthenticatedFails() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/edit');

        $this->assertRedirectContains('/login');
    }

    public function testEdit() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->get('/things/edit/00000000-0000-0000-0000-000000000001');
        $this->assertResponseOk();
        // form element and attributes
        $this->assertResponseContains('<form');
        $this->assertResponseContains('action="/things/edit/00000000-0000-0000-0000-000000000001"');
        // $this->assertResponseContains('data-panels-url="/api/things/panels"');
        // submit button
        $this->assertResponseContains('type="submit"');
        // input element(s) and attributes
        $this->assertResponseContains('name');
        $this->assertResponseContains('name="Things[name]"');
    }

    public function testEditPostData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'type' => 'a',
            'name' => 'test'
        ];

        $this->post('/things/edit/' . $id, $data);
        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('things')->get($id);

        $this->assertEquals($data['name'], $entity->get('name'));
    }

    public function testEditPutData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'type' => 'a',
            'name' => 'test'
        ];

        $this->put('/things/edit/' . $id, $data);
        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('things')->get($id);

        $this->assertEquals($data['name'], $entity->get('name'));
    }

    public function testDeleteUnauthenticatedFails() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->session(['Auth' => '']);

        // No session data set.
        $this->delete('/things/delete/00000000-0000-0000-0000-000000000001');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testDeleteGetRequest() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->get('/things/delete/00000000-0000-0000-0000-000000000001');
        Configure::read("debug") ? $this->assertResponseError() : $this->assertRedirect();
    }

    public function testDeleteData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/things/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('things')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testDeletePostData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/things/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('things')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testBatchGetRequest() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->get('/things/batch/edit');
        Configure::read("debug") ? $this->assertResponseError() : $this->assertRedirect();
    }

    public function testBatchDelete() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $table = TableRegistry::get('things');
        $initialCount = $table->find('all')->count();

        $data = [
            'batch' => [
                'ids' => [
                    '00000000-0000-0000-0000-000000000001',
                    '00000000-0000-0000-0000-000000000002'
                ]
            ]
        ];

        $this->post('/things/batch/delete', $data);
        $this->assertResponseSuccess();
        $this->assertSession('2 of 2 selected records have been deleted.', 'Flash.flash.0.message');

        $this->assertSame($initialCount - 2, $table->find('all')->count());
    }

    public function testBatchDeleteNoIds() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->post('/things/batch/delete');
        $this->assertRedirect('/');
        $this->assertSession('No records selected.', 'Flash.flash.0.message');
    }

    public function testBatchEdit() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $data = [
            'batch' => [
                'ids' => [
                    '00000000-0000-0000-0000-000000000001',
                    '00000000-0000-0000-0000-000000000002'
                ]
            ]
        ];
        $this->post('/things/batch/edit', $data);
        $this->assertResponseSuccess();

        $entity = $this->viewVariable('entity');

        $this->assertInstanceOf(Thing::class, $entity);
        $this->assertTrue($entity->isNew());
    }

    public function testBatchEditNoIds() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $this->post('/things/batch/edit');
        $this->assertRedirect('/');
        $this->assertSession('No records selected.', 'Flash.flash.0.message');
    }

    public function testBatchEditExecuteNoIds() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $data = [
            'batch' => [
                'execute' => true
            ]
        ];

        $this->post('/things/batch/edit', $data);
        $this->assertRedirect('/');
        $this->assertSession('No records selected.', 'Flash.flash.0.message');
    }

    public function testBatchEditExecuteNoData() : void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        $data = [
            'batch' => [
                'execute' => true,
                'ids' => [
                    '00000000-0000-0000-0000-000000000001',
                    '00000000-0000-0000-0000-000000000002'
                ]
            ]
        ];

        $this->post('/things/batch/edit', $data);
        $this->assertResponseSuccess();
        $this->assertSession('Selected records could not be updated. No changes provided.', 'Flash.flash.0.message');
    }
}
