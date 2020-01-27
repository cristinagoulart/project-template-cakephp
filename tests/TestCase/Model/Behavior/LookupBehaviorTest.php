<?php

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\LookupBehavior;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Webmozart\Assert\Assert;

/**
 * App\Model\Behavior\LookupBehavior Test Case
 */
class LookupBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Behavior\LookupBehavior
     */
    public $Lookup;

    /**
     * Thinks table
     * @var \Cake\ORM\Table
     */
    public $things;

    /**
     * Users table
     * @var \Cake\ORM\Table
     */
    public $users;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.log_audit',
        'app.things',
        'app.users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->things = TableRegistry::getTableLocator()->get('Things');
        $this->users = TableRegistry::getTableLocator()->get('Users');

        $config = [
            'lookupFields' => [
                'name',
            ],
        ];

        $this->Lookup = new LookupBehavior($this->things, $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Lookup);
        unset($this->things);
        unset($this->users);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testBeforeFind(): void
    {
        $expected = 'Thing #2';
        $query = $this->things->find();

        $this->Lookup->beforeFind(
            new Event('Model.beforeFind', $this->things, ['query' => $query]),
            $query,
            new ArrayObject(['lookup' => true, 'value' => $expected]),
            true
        );

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame('00000000-0000-0000-0000-000000000002', $entity->get('id'));
        $this->assertRegExp('/WHERE \(\(`Things`.`name` = :c0\) AND \(`Things`.`trashed`\) IS NULL\)/', $query->sql());
        $this->assertSame([$expected], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testBeforeFindWithoutPrimaryQuery(): void
    {
        $query = $this->things->query();

        $this->Lookup->beforeFind(
            new Event('Model.beforeFind', $this->things),
            $query,
            new ArrayObject(['lookup' => true, 'value' => 'Thing #2']),
            false
        );

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame('00000000-0000-0000-0000-000000000001', $entity->get('id'));
        $this->assertRegExp('/WHERE \(`Things`.`trashed`\) IS NULL/', $query->sql());
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testBeforeFindWithoutLookupValue(): void
    {
        $query = $this->things->query();

        $this->Lookup->beforeFind(
            new Event('Model.beforeFind', $this->things),
            $query,
            new ArrayObject(['lookup' => true]),
            true
        );

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame('00000000-0000-0000-0000-000000000001', $entity->get('id'));
        $this->assertRegExp('/WHERE \(`Things`.`trashed`\) IS NULL/', $query->sql());
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testBeforeFindWithoutLookupFields(): void
    {
        $query = $this->things->query();

        $behavior = new LookupBehavior($this->things, []);

        $behavior->beforeFind(
            new Event('Model.beforeFind', $this->things),
            $query,
            new ArrayObject(['lookup' => true, 'value' => 'Thing #2']),
            true
        );

        $this->assertRegExp('/`Things`.`id` = :c0/', $query->sql());
        $this->assertSame(['Thing #2'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['uuid'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testBeforeMarshal(): void
    {
        $table = TableRegistry::getTableLocator()->get('Users');
        $table->deleteAll([]);
        $username = 'john_snow';
        $data = new ArrayObject(['assigned_to' => $username]);

        $entity = $table->newEntity(['username' => $username, 'password' => 'foobar']);
        $table->save($entity);
        $this->assertNotNull($entity->get('id'));

        $this->Lookup->beforeMarshal(
            new Event('Model.beforeMarshal', $this->things),
            $data,
            new ArrayObject(['lookup' => true])
        );

        $this->assertSame($entity->get('id'), $data['assigned_to']);
    }

    public function testBeforeMarshalWithNonExistingRecord(): void
    {
        $table = TableRegistry::getTableLocator()->get('Users');
        $table->deleteAll([]);
        $data = new ArrayObject(['assigned_to' => 'non_existing_record']);

        $entity = $table->newEntity(['username' => 'john_snow', 'password' => 'foobar']);
        $table->save($entity);
        $this->assertNotNull($entity->get('id'));

        $this->Lookup->beforeMarshal(
            new Event('Model.beforeMarshal', $this->things),
            $data,
            new ArrayObject(['lookup' => true])
        );

        $this->assertSame('non_existing_record', $data['assigned_to']);
    }

    public function testBeforeMarshalWithValidId(): void
    {
        $table = TableRegistry::getTableLocator()->get('Users');
        $table->deleteAll([]);
        $username = 'john_snow';
        $data = new ArrayObject(['assigned_to' => $username]);

        $entity = $table->newEntity(['username' => $username, 'password' => 'foobar']);
        $table->save($entity);

        $this->Lookup->beforeMarshal(
            new Event('Model.beforeMarshal', $this->things),
            $data,
            new ArrayObject(['lookup' => true])
        );

        $this->assertSame($entity->get('id'), $data['assigned_to']);
    }

    public function testBeforeMarshalWithoutLookupFields(): void
    {
        $this->things->deleteAll([]);

        $behavior = new LookupBehavior($this->things, []);
        $name = 'foobar';

        $entity = $this->things->newEntity(['name' => $name], ['validate' => false]);
        $this->things->save($entity);
        $this->assertNotNull($entity->get('id'));

        $data = new ArrayObject(['primary_thing' => $name]);
        $behavior->beforeMarshal(
            new Event('Model.beforeMarshal', $this->things),
            $data,
            new ArrayObject(['lookup' => true])
        );

        $this->assertSame(['primary_thing' => $name], $data->getArrayCopy());
    }

    public function testUsersLookup(): void
    {
        $expected = 'user-1@test.com';
        $query = $this->users->find('all')->applyOptions(['lookup' => true, 'value' => $expected]);

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame($expected, $entity->get('email'));
        $this->assertRegExp('/WHERE \(\(`Users`.`email` = :c0 OR `Users`.`username` = :c1\) AND/', $query->sql());
        $this->assertSame([$expected, $expected], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string', 'string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testfindLookup(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-1@test.com']);

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame('user-1@test.com', $entity->get('email'));
        $this->assertRegExp('/WHERE \(\(`Users`.`email` = :c0 OR `Users`.`username` = :c1\) AND/', $query->sql());
        $this->assertSame(['user-1@test.com', 'user-1@test.com'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string', 'string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testfindLookupWithWhere(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-1@test.com'])->where(['username' => 'user-1']);

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertSame('user-1@test.com', $entity->get('email'));
        $this->assertRegExp('/WHERE \(\(`Users`.`email` = :c0 OR `Users`.`username` = :c1\) AND `username` = :c2/', $query->sql());
        $this->assertSame(['user-1@test.com', 'user-1@test.com', 'user-1'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string', 'string', 'string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testfindLookupWithWhereFailed(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-2@test.com'])->where(['username' => 'user-1']);

        $this->assertNull($query->first());
        $this->assertRegExp('/WHERE \(\(`Users`.`email` = :c0 OR `Users`.`username` = :c1\) AND `username` = :c2/', $query->sql());
        $this->assertSame(['user-2@test.com', 'user-2@test.com', 'user-1'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string', 'string', 'string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testfindLookupWithoutValueInOptions(): void
    {
        $query = $this->users->find('lookup');

        $this->assertInstanceOf(\App\Model\Entity\User::class, $query->firstOrFail());
        $this->assertRegExp('/WHERE \(`Users`.`trashed`\) IS NULL/', $query->sql());
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame([], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testfindLookupWithNonExistentRecord(): void
    {
        $query = $this->users->find('lookup', ['value' => '00000000-0000-0000-0000-000000000002']);

        $this->assertNull($query->first());
        $this->assertRegExp('/WHERE \(\(`Users`.`email` = :c0 OR `Users`.`username` = :c1\) AND/', $query->sql());
        $this->assertSame(['00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000002'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['string', 'string'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }

    public function testFindLookupWithoutLookupFields(): void
    {
        $query = $this->things->query();

        $behavior = new LookupBehavior($this->things, []);

        $behavior->findLookup($query, ['value' => 'Thing #2']);

        $this->assertRegExp('/`Things`.`id` = :c0/', $query->sql());
        $this->assertSame(['Thing #2'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));
        $this->assertSame(['uuid'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }
}
