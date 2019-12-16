<?php

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class UsersControllerTest extends IntegrationTestCase
{
    /**
     * @var string User ID
     */
    private $userId;

    /**
     * @var \Cake\ORM\Table Table instance
     */
    private $table;

    public $fixtures = [
        'app.log_audit',
        'app.users',
        'plugin.Groups.groups',
        'plugin.Groups.groups_users',
        'plugin.Menu.menus',
        'plugin.Menu.menu_items',
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

    private function withSession(): void
    {
        $this->session([
            'Auth' => [
                'User' => $this->table->get($this->userId)->toArray(),
            ],
        ]);
    }

    public function testLogin(): void
    {
        $this->get('/users/login');
        $this->assertResponseOk();

        $this->get('/login');
        $this->assertResponseOk();
    }

    public function testRegister(): void
    {
        if (! Configure::read('Users.Registration.active')) {
            $this->markTestSkipped('User registration is inactive.');
        }

        $this->get('/users/register');
        $this->assertResponseOk();
    }

    public function testRequestResetPassword(): void
    {
        $this->get('/users/requestResetPassword');
        $this->assertResponseOk();
    }

    public function testResetPassword(): void
    {
        $this->enableRetainFlashMessages();

        $this->get('/users/ResetPassword');
        $this->assertRedirect();
        $this->assertSession('Invalid token or user account already validated', 'Flash.flash.0.message');
    }

    public function testIndex(): void
    {
        $this->withSession();

        $this->get('/users');
        $this->assertResponseOk();

        $this->get('/users/index');
        $this->assertResponseOk();
    }

    public function testIndexWithoutSession(): void
    {
        $this->get('/users');
        $this->assertRedirect();
    }

    public function testView(): void
    {
        $this->withSession();

        $this->get('/users/view/' . $this->userId);
        $this->assertResponseOk();
    }

    public function testViewWithoutSession(): void
    {
        $this->get('/users/view/' . $this->userId);
        $this->assertRedirect();
    }

    public function testProfile(): void
    {
        $this->withSession();

        $this->get('/users/profile/');
        $this->assertResponseOk();
    }

    public function testProfileWithoutSession(): void
    {
        $this->get('/users/profile/');
        $this->assertRedirect();
    }

    public function testProfileEdit(): void
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

    public function testProfileEditWithoutSession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = ['username' => md5('Some really really random username')];

        $this->put('/users/edit-profile/', $data);
        $entity = $this->table->get($this->userId);
        $this->assertRedirect();
        $this->assertNotEquals($data['username'], $entity->get('username'));
    }

    public function testChangeUserPassword(): void
    {
        $this->enableSecurityToken();
        $this->enableCsrfToken();
        $data = [
            'password' => 'cakephp',
            'password_confirm' => 'cakephp',
        ];

        $this->withSession();

        $this->get('/users/change-user-password/' . $this->userId);
        $this->assertResponseOk();

        $this->post('/users/change-user-password/' . $this->userId, $data);
        $this->assertRedirect();
    }

    public function testChangeUserPasswordWithoutSession(): void
    {
        $this->enableSecurityToken();
        $this->enableCsrfToken();
        $data = [
            'password' => 'cakephp',
            'password_confirm' => 'cakephp',
        ];

        $this->get('/users/change-user-password/' . $this->userId);
        $this->assertRedirect();

        $this->post('/users/change-user-password/' . $this->userId, $data);
        $this->assertRedirect();
    }

    public function testChangeUserPasswordWithInvalidData(): void
    {
        $this->enableSecurityToken();
        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
        $data = [
            'password' => 'cakephp 3',
            'password_confirm' => 'cakephp',
        ];
        $emptyData = [
            'password' => '',
            'password_confirm' => '',
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

    public function testAdd(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $data = [
            'username' => 'john.smith',
            'password' => 'john.smith',
            'email' => 'john.smith@company.com',
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

    public function testAddWithInvalidData(): void
    {
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

    public function testAddWithoutSession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'username' => 'john.smith',
            'password' => 'john.smith',
            'email' => 'john.smith@company.com',
        ];

        $count = $this->table->find()->count();

        $this->post('/users/add/', $data);
        $this->assertRedirect();
        $this->assertEquals($count, $this->table->find()->count());
    }

    public function testEdit(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $data = ['username' => 'john.smith'];
        $this->get('/users/edit/' . $this->userId . '?username=john.smith');
        $this->assertResponseOk();

        $this->put('/users/edit/' . $this->userId, $data);
        $this->assertRedirect();

        $entity = $this->table->get($this->userId);
        $this->assertEquals($data['username'], $entity->get('username'));
    }

    public function testEditWithInvalidData(): void
    {
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

    public function testEditWithoutSession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = ['username' => 'john.smith'];

        $entity = $this->table->get($this->userId);

        $this->put('/users/edit/' . $this->userId, $data);
        $this->assertRedirect();
        $this->assertEquals($entity, $this->table->get($this->userId));
    }

    public function testDeleteAnyUser(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->delete('/users/delete/' . $userId);
        $this->assertRedirect();

        $query = $this->table->find()->where(['id' => $userId]);
        $this->assertTrue($query->isEmpty());
    }

    public function testDeleteSameUser(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->withSession();

        $this->disableErrorHandlerMiddleware();
        $this->expectException(ForbiddenException::class);
        $this->delete('/users/delete/' . $this->userId);
    }

    public function testDeleteWithoutSession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->delete('/users/delete/' . $userId);
        $this->assertRedirect();

        $query = $this->table->find()->where(['id' => $userId]);
        $this->assertFalse($query->isEmpty());
    }
}
