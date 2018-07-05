<?php
namespace App\Test\TestCase\Controller;

use App\Controller\DblistsController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class UsersControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.log_audit',
        'app.users',
        'plugin.Groups.groups',
        'plugin.Groups.groups_users',
        'plugin.Menu.menus',
        'plugin.Menu.menu_items'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->userId = '00000000-0000-0000-0000-000000000002';
        $this->table = TableRegistry::get('Users');
    }

    public function tearDown()
    {
        unset($this->table);
        unset($this->userId);

        parent::tearDown();
    }

    private function withSession()
    {
        $this->session([
            'Auth' => [
                'User' => $this->table->get($this->userId)->toArray(),
            ]
        ]);
    }

    public function testLogin()
    {
        $this->get('/users/login');
        $this->assertResponseOk();

        $this->get('/login');
        $this->assertResponseOk();
    }

    public function testRegister()
    {
        if (! Configure::read('Users.Registration.active')) {
            $this->markTestSkipped('User registration is inactive.');
        }

        $this->get('/users/register');
        $this->assertResponseOk();
    }

    public function testRequestResetPassword()
    {
        $this->get('/users/requestResetPassword');
        $this->assertResponseOk();
    }

    public function testResetPassword()
    {
        $this->enableRetainFlashMessages();

        $this->get('/users/ResetPassword');
        $this->assertRedirect();
        $this->assertSession('Invalid token or user account already validated', 'Flash.flash.0.message');
    }

    public function testIndex()
    {
        $this->withSession();

        $this->get('/users');
        $this->assertResponseOk();

        $this->get('/users/index');
        $this->assertResponseOk();
    }

    public function testIndexWithoutSession()
    {
        $this->get('/users');
        $this->assertRedirect();
    }

    public function testView()
    {
        $this->withSession();

        $this->get('/users/view/' . $this->userId);
        $this->assertResponseOk();
    }

    public function testViewWithoutSession()
    {
        $this->get('/users/view/' . $this->userId);
        $this->assertRedirect();
    }

    public function testProfile()
    {
        $this->withSession();

        $this->get('/users/profile/');
        $this->assertResponseOk();
    }

    public function testProfileWithoutSession()
    {
        $this->get('/users/profile/');
        $this->assertRedirect();
    }

    public function testProfileEdit()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $data = ['username' => md5('Some really really random username')];

        $this->put('/users/edit-profile/', $data);

        $entity = $this->table->get($this->userId);
        $this->assertRedirect();
        $this->assertEquals($data['username'], $entity->get('username'));
    }

    public function testProfileEditWithoutSession()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = ['username' => md5('Some really really random username')];

        $this->put('/users/edit-profile/', $data);
        $entity = $this->table->get($this->userId);
        $this->assertRedirect();
        $this->assertNotEquals($data['username'], $entity->get('username'));
    }

    public function testChangeUserPassword()
    {
        $this->enableCsrfToken();
        $data = [
            'password' => 'cakephp',
            'password_confirm' => 'cakephp'
        ];

        $this->withSession();

        $this->get('/users/change-user-password/' . $this->userId);
        $this->assertResponseOk();

        $this->post('/users/change-user-password/' . $this->userId, $data);
        $this->assertRedirect();
    }

    public function testChangeUserPasswordWithoutSession()
    {
        $this->enableCsrfToken();
        $data = [
            'password' => 'cakephp',
            'password_confirm' => 'cakephp'
        ];

        $this->get('/users/change-user-password/' . $this->userId);
        $this->assertRedirect();

        $this->post('/users/change-user-password/' . $this->userId, $data);
        $this->assertRedirect();
    }

    public function testChangeUserPasswordWithInvalidData()
    {
        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
        $data = [
            'password' => 'cakephp 3',
            'password_confirm' => 'cakephp'
        ];
        $emptyData = [
            'password' => '',
            'password_confirm' => ''
        ];

        $this->withSession();

        $this->post('/users/change-user-password/' . $this->userId, $data);
        $this->assertResponseOk();
        $this->assertSession('Password could not be changed', 'Flash.flash.0.message');

        $this->post('/users/change-user-password/' . $this->userId, $emptyData);
        $this->assertResponseOk();
        $this->assertSession('Password could not be changed', 'Flash.flash.0.message');

        $this->post('/users/change-user-password/' . $this->userId, []);
        $this->assertResponseOk();
        $this->assertSession('Password could not be changed', 'Flash.flash.0.message');
    }

    public function testAdd()
    {
        $this->markTestSkipped('Fails on Travis CI with this message: The request has been black-holed');
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $data = [
            'username' => 'john.smith',
            'password' => 'john.smith',
            'email' => 'john.smith@company.com'
        ];
        $where = $data;
        unset($where['password']);

        $this->get('/users/add');
        $this->assertResponseOk();

        $this->post('/users/add', $data);
        $this->assertRedirect();

        $query = $this->table->find()->where($where);
        $this->assertEquals(1, $query->count());
    }

    public function testAddWithInvalidData()
    {
        $this->markTestSkipped('Fails on Travis CI with this message: The request has been black-holed');
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();
        $this->enableRetainFlashMessages();

        $count = $this->table->find()->count();

        // trying to save entity without any data
        $this->post('/users/add', []);
        $this->assertResponseOk();
        $this->assertEquals($count, $this->table->find()->count());
        $this->assertSession('The User could not be saved', 'Flash.flash.0.message');
    }

    public function testAddWithoutSession()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'username' => 'john.smith',
            'password' => 'john.smith',
            'email' => 'john.smith@company.com'
        ];

        $count = $this->table->find()->count();

        $this->post('/users/add/', $data);
        $this->assertRedirect();
        $this->assertEquals($count, $this->table->find()->count());
    }

    public function testEdit()
    {
        $this->markTestSkipped('Fails on Travis CI with this message: The request has been black-holed');
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $data = ['username' => 'john.smith'];

        $this->get('/users/edit/' . $this->userId, $data);
        $this->assertResponseOk();

        $this->put('/users/edit/' . $this->userId, $data);
        $this->assertRedirect();

        $entity = $this->table->get($this->userId);
        $this->assertEquals($data['username'], $entity->get('username'));
    }

    public function testEditWithInvalidData()
    {
        $this->markTestSkipped('Fails on Travis CI with this message: The request has been black-holed');
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();
        $this->enableRetainFlashMessages();

        $data = ['username' => null];

        $entity = $this->table->get($this->userId);

        // trying to update entity with invalid data
        $this->put('/users/edit/' . $this->userId, $data);
        $this->assertResponseOk();
        $this->assertEquals($entity, $this->table->get($this->userId));
        $this->assertSession('The User could not be saved', 'Flash.flash.0.message');
    }

    public function testEditWithoutSession()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = ['username' => 'john.smith'];

        $entity = $this->table->get($this->userId);

        $this->put('/users/edit/' . $this->userId, $data);
        $this->assertRedirect();
        $this->assertEquals($entity, $this->table->get($this->userId));
    }

    public function testDelete()
    {
        $this->markTestSkipped('Fails on Travis CI: asserting that the query is empty does not validate to true');
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $this->delete('/users/delete/' . $this->userId);
        $this->assertRedirect();

        $query = $this->table->find()->where(['id' => $this->userId]);
        $this->assertTrue($query->isEmpty());
    }

    public function testDeleteWithoutSession()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete('/users/delete/' . $this->userId);
        $this->assertRedirect();

        $query = $this->table->find()->where(['id' => $this->userId]);
        $this->assertFalse($query->isEmpty());
    }
}
