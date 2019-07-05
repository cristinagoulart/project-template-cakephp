<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Thing;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Test\App\Controller\ThingsController Test Case
 */
class ThingsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.things',
        'app.log_audit',
        'plugin.CakeDC/Users.users',
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
        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/view/00000000-0000-0000-0000-000000000001');
        $this->assertRedirectContains('/login');
    }

    public function testView() : void
    {
        $this->get('/things/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
    }

    public function testAddUnauthenticatedFails() : void
    {
        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/add');

        $this->assertRedirectContains('/login');
    }

    public function testAdd() : void
    {
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
        $data = [
            'type' => 'a',
            'name' => 'test'
        ];

        $this->post('/things/add', $data);
        $this->assertResponseSuccess();

        // fetch new record
        $query = TableRegistry::get('things')->find()->where(['name' => $data['name']]);

        $this->assertEquals(1, $query->count());
    }

    public function testEditUnauthenticatedFails() : void
    {
        $this->session(['Auth' => '']);

        // No session data set.
        $this->get('/things/edit');

        $this->assertRedirectContains('/login');
    }

    public function testEdit() : void
    {
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
        $this->session(['Auth' => '']);

        // No session data set.
        $this->delete('/things/delete/00000000-0000-0000-0000-000000000001');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testDeleteGetRequest() : void
    {
        $this->get('/things/delete/00000000-0000-0000-0000-000000000001');
        $this->assertRedirect();
    }

    public function testDeleteData() : void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/things/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('things')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testDeletePostData() : void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/things/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('things')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testBatchGetRequest() : void
    {
        $this->get('/things/batch/edit');
        $this->assertRedirect();
    }

    public function testBatchDelete() : void
    {
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
        $this->post('/things/batch/delete');
        $this->assertRedirect('/');
        $this->assertSession('No records selected.', 'Flash.flash.0.message');
    }

    public function testBatchEdit() : void
    {
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
        $this->post('/things/batch/edit');
        $this->assertRedirect('/');
        $this->assertSession('No records selected.', 'Flash.flash.0.message');
    }

    public function testBatchEditExecuteNoIds() : void
    {
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
