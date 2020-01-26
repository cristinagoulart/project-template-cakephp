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
        $query = $this->things->find();
        $event = new Event('Model.beforeFind', $this->things, [
            'query' => $query,
        ]);

        $options = new ArrayObject([
            'lookup' => true,
            'value' => 'Thing #2',
        ]);

        $primary = true;
        $this->Lookup->beforeFind($event, $query, $options, $primary);
        $entity = $query->firstOrFail();

        $id = is_array($entity) ?: $entity->get('id');

        $this->assertEquals('00000000-0000-0000-0000-000000000002', $id);
        $this->assertRegExp('/`Things`.`name` = :c0/', $query->sql());
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

        $this->assertEquals('00000000-0000-0000-0000-000000000001', $entity->get('id'));
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

        $this->assertEquals('00000000-0000-0000-0000-000000000001', $entity->get('id'));
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

        $this->assertEquals(['Thing #2'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));

        $this->assertEquals(['uuid'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
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
        $query = $this->users->find('all')->applyOptions(['lookup' => true, 'value' => 'user-1@test.com'])->firstOrFail();

        $email = is_array($query) ?: $query->get('email');

        $this->assertSame('user-1@test.com', $email);
    }

    public function testfindLookup(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-1@test.com'])->first();
        $this->assertInstanceOf('App\Model\Entity\User', $query);
    }

    public function testfindLookupWithWhere(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-1@test.com'])->where(['username' => 'user-1'])->first();
        $this->assertInstanceOf('App\Model\Entity\User', $query);
    }

    public function testfindLookupWithWhereFailed(): void
    {
        $query = $this->users->find('lookup', ['value' => 'user-2@test.com'])->where(['username' => 'user-1'])->first();
        $this->assertNull($query);
    }

    public function testfindLookupWithoutValueInOptions(): void
    {
        $query = $this->users->find('lookup')->firstOrFail();
        $this->assertInstanceOf('App\Model\Entity\User', $query);
    }

    public function testfindLookupWithNonExistentRecord(): void
    {
        $query = $this->users->find('lookup', ['value' => '00000000-0000-0000-0000-000000000002'])->first();
        $this->assertNull($query);
    }

    public function testFindLookupWithoutLookupFields(): void
    {
        $query = $this->things->query();

        $behavior = new LookupBehavior($this->things, []);

        $behavior->findLookup($query, ['value' => 'Thing #2']);

        $this->assertRegExp('/`Things`.`id` = :c0/', $query->sql());

        $this->assertEquals(['Thing #2'], Hash::extract($query->getValueBinder()->bindings(), '{s}.value'));

        $this->assertEquals(['uuid'], Hash::extract($query->getValueBinder()->bindings(), '{s}.type'));
    }
}
