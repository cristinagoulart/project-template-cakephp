<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Webmozart\Assert\Assert;

class UsersTableTest extends TestCase
{
    public $fixtures = [
        'app.users',
    ];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Users');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testInitialize(): void
    {
        $this->assertInstanceOf(UsersTable::class, $this->table);
        $this->assertSame(true, $this->table->hasBehavior('Searchable'));
        $this->assertSame(true, $this->table->hasBehavior('Footprint'));
        $this->assertSame(true, $this->table->hasBehavior('Lookup'));
        $this->assertSame('id', $this->table->getPrimaryKey());
        $this->assertSame('name', $this->table->getDisplayField());
    }

    public function testValidationDefault(): void
    {
        $validator = $this->table->validationDefault(new Validator());

        $this->assertSame(true, $validator->hasField('username'));
        $this->assertSame(true, $validator->hasField('first_name'));
        $this->assertSame(true, $validator->hasField('last_name'));

        $this->assertSame(true, $validator->field('username')->offsetExists('validRegex'));
        $this->assertSame(true, $validator->field('first_name')->offsetExists('validRegex'));
        $this->assertSame(true, $validator->field('last_name')->offsetExists('validRegex'));

        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar']);
        $this->assertSame([], $entity->getErrors());

        $this->table->save($entity);
        $this->assertNotNull($entity->get('id'));
    }

    public function testValidationInvalidUsername(): void
    {
        //Check for exclamation mark
        $entity = $this->table->newEntity(['username' => 'foobar!', 'password' => 'foobar']);

        $this->assertSame(true, array_key_exists('validRegex', $entity->getError('username')));
        $this->assertSame(false, $this->table->save($entity));

        //Check for space in username
        $entity = $this->table->newEntity(['username' => 'foo bar', 'password' => 'foobar']);

        $this->assertSame(true, array_key_exists('validRegex', $entity->getError('username')));
        $this->assertSame(false, $this->table->save($entity));
    }

    public function testValidationInvalidFirstName(): void
    {
        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar', 'first_name' => 'john=']);

        $this->assertSame(true, array_key_exists('validRegex', $entity->getError('first_name')));
        $this->assertSame(false, $this->table->save($entity));
    }

    public function testValidationInvalidLastName(): void
    {
        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar', 'last_name' => 'snow!']);

        $this->assertSame(true, array_key_exists('validRegex', $entity->getError('last_name')));
        $this->assertSame(false, $this->table->save($entity));
    }

    public function testFindAuth(): void
    {
        $query = $this->table->query();

        $this->table->findAuth($query, []);

        $this->assertRegExp('/`Users`.`active` = :c0/', $query->sql());
        $this->assertSame([1], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['boolean'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testIsCustomAvatarExists(): void
    {
        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar']);

        $this->table->save($entity);
        $this->assertSame(false, $this->table->isCustomAvatarExists($entity));

        $this->createCustomAvatar($entity->get('id'));
        $this->assertSame(true, $this->table->isCustomAvatarExists($entity));
        $this->deleteCustomAvatar($entity->get('id'));
    }

    public function testCopyCustomAvatar(): void
    {
        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar']);

        $this->table->save($entity);
        $this->assertSame(false, $this->table->copyCustomAvatar($entity));

        $this->createCustomAvatar($entity->get('id'));
        $this->assertSame(true, $this->table->copyCustomAvatar($entity));
        $this->deleteAvatar($entity->get('id'));
        $this->deleteCustomAvatar($entity->get('id'));
    }

    public function testSaveCustomAvatar(): void
    {
        $entity = $this->table->newEntity(['username' => 'foobar', 'password' => 'foobar']);

        $this->table->save($entity);

        $this->assertSame(true, $this->table->saveCustomAvatar($entity, imagecreatefrompng(WWW_ROOT . 'img' . DS . 'cake-logo.png')));
        $this->deleteAvatar($entity->get('id'));
        $this->deleteCustomAvatar($entity->get('id'));
    }

    private function createCustomAvatar(string $id): void
    {
        Assert::uuid($id);
        $path = WWW_ROOT . 'img' . DS . 'cake-logo.png';
        Assert::fileExists($path);

        Assert::true(copy($path, $this->getAvatarCustomDirectory() . $id . '.png'));
    }

    private function deleteAvatar(string $id): void
    {
        Assert::uuid($id);
        $path = $this->getAvatarDirectory() . $id . '.png';

        Assert::true(unlink($path));
    }

    private function deleteCustomAvatar(string $id): void
    {
        Assert::uuid($id);
        $path = $this->getAvatarCustomDirectory() . $id . '.png';

        Assert::true(unlink($path));
    }

    private function getAvatarCustomDirectory(): string
    {
        $result = WWW_ROOT . Configure::readOrFail('Avatar.customDirectory');
        Assert::string($result);

        $result = rtrim($result, DS) . DS;
        Assert::fileExists($result);

        return $result;
    }

    private function getAvatarDirectory(): string
    {
        $result = WWW_ROOT . Configure::readOrFail('Avatar.directory');
        Assert::string($result);

        $result = rtrim($result, DS) . DS;
        Assert::fileExists($result);

        return $result;
    }
}
