<?php
namespace App\Test\TestCase\Entity;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Webmozart\Assert\Assert;

class ContactTest extends TestCase
{
    public $fixtures = [
        'app.users'
    ];

    private $table;
    private $primaryKey;

    public function setUp() : void
    {
        parent::setUp();

        $this->table = TableRegistry::get('Users');
        $this->primaryKey = $this->table->getPrimaryKey();
        Assert::string($this->primaryKey);
    }

    public function tearDown() : void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testVirtualFieldName() : void
    {
        $entity = $this->table->newEntity(['first_name' => 'John', 'last_name' => 'Snow', 'username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame('John Snow', $entity->get('name'));
    }

    public function testVirtualFieldNameWithoutFirstName() : void
    {
        $entity = $this->table->newEntity(['last_name' => 'Snow', 'username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame('Snow', $entity->get('name'));
    }

    public function testVirtualFieldNameWithoutLastName() : void
    {
        $entity = $this->table->newEntity(['first_name' => 'John', 'username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame('John', $entity->get('name'));
    }

    public function testVirtualFieldNameWithoutFirstNameLastName() : void
    {
        $entity = $this->table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame('john_snow', $entity->get('name'));
    }

    public function testVirtualFieldImageSrc() : void
    {
        $entity = $this->table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame(sprintf('/uploads/avatars/%s.png', $entity->get('id')), $entity->get('image_src'));
    }

    public function testVirtualFieldIsAdmin() : void
    {
        $entity = $this->table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame(false, $entity->get('is_admin'));
    }

    public function testVirtualFieldIsAdminWithIsSuperuser() : void
    {
        $entity = $this->table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $entity->set('is_superuser', true);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame(true, $entity->get('is_admin'));
    }

    public function testVirtualFieldIsAdminWithInvalidConfiguration() : void
    {
        Configure::write('RolesCapabilities.Roles.Admin.name', ['invalid configuration']);
        $entity = $this->table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $this->table->save($entity);

        $entity = $this->table->get($entity->get($this->primaryKey));

        $this->assertSame(false, $entity->get('is_admin'));
    }
}
