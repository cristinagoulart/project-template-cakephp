<?php
namespace App\Test\TestCase\ORM;

use App\ORM\LabeledFormatter;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LabeledFormatterTest extends TestCase
{
    public $fixtures = [
        'app.LogAudit',
        'app.Things',
        'app.Users'
    ];

    private $table;

    public function setUp() : void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Things');
    }

    public function tearDown() : void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testFormatResults() : void
    {
        $expected = [
            'appointment' => '2019-10-29 15:47:16',
            'area_amount' => 25.74,
            'area_unit' => 'm²',
            'assigned_to' => 'user-2',
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47:16',
            'created_by' => 'user-1',
            'date_of_birth' => '1990-01-17',
            'description' => 'Long description goes here',
            'email' => '1@thing.com',
            'file' => null,
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => 7,
            'modified' => '2018-01-18 15:47:16',
            'modified_by' => 'user-1',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => null,
            'primary_thing' => 'Thing #2',
            'rate' => 25.13,
            'salary_amount' => 1000.0,
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'sample_date' => null,
            'test_list' => '',
            'testmetric_amount' => 33.18,
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => 155.22,
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
            'title' => 'Dr',
            'trashed' => null,
            'vip' => true,
            'website' => 'https://google.com',
            'work_start' => '2019-11-19 08:32:00'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new LabeledFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithContain() : void
    {
        $expected = [
            'appointment' => '2019-10-29 15:47:16',
            'area_amount' => 25.74,
            'area_unit' => 'm²',
            'assigned_to' => 'user-2',
            'assigned_to_user' => [
                'id' => '00000000-0000-0000-0000-000000000002',
                'username' => 'user-2',
                'email' => 'user-2@test.com',
                'first_name' => 'user',
                'last_name' => 'second',
                'activation_date' => '2015-06-24 17:33:54',
                'secret' => 'xxx',
                'secret_verified' => false,
                'tos_date' => '2015-06-24 17:33:54',
                'active' => true,
                'is_superuser' => true,
                'role' => 'admin',
                'created' => '2015-06-24 17:33:54',
                'modified' => '2015-06-24 17:33:54',
                'country' => null,
                'initials' => null,
                'gender' => null,
                'phone_office' => null,
                'phone_home' => null,
                'phone_mobile' => null,
                'birthdate' => null,
                'image' => null,
                'extras' => null,
                'is_supervisor' => false,
                'company' => null,
                'department' => null,
                'team' => null,
                'position' => null,
                'phone_extension' => null,
                'reports_to' => null,
                'fax' => null,
                'additional_data' => null,
                'trashed' => null,
                'name' => 'user second',
                'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                'is_admin' => true
            ],
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47:16',
            'created_by' => 'user-1',
            'date_of_birth' => '1990-01-17',
            'description' => 'Long description goes here',
            'email' => '1@thing.com',
            'file' => null,
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => 7,
            'modified' => '2018-01-18 15:47:16',
            'modified_by' => 'user-1',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => null,
            'primary_thing' => 'Thing #2',
            'rate' => 25.13,
            'salary_amount' => 1000.0,
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'sample_date' => null,
            'test_list' => '',
            'testmetric_amount' => 33.18,
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => 155.22,
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
            'title' => 'Dr',
            'trashed' => null,
            'vip' => true,
            'website' => 'https://google.com',
            'work_start' => '2019-11-19 08:32:00'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->contain('AssignedToUsers')
            ->formatResults(new LabeledFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithMatching() : void
    {
        $expected = [
            '_matchingData' => [
                'AssignedToUsers' => [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'username' => 'user-2',
                    'email' => 'user-2@test.com',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'activation_date' => '2015-06-24 17:33:54',
                    'secret' => 'xxx',
                    'secret_verified' => false,
                    'tos_date' => '2015-06-24 17:33:54',
                    'active' => true,
                    'is_superuser' => true,
                    'role' => 'admin',
                    'created' => '2015-06-24 17:33:54',
                    'modified' => '2015-06-24 17:33:54',
                    'country' => null,
                    'initials' => null,
                    'gender' => null,
                    'phone_office' => null,
                    'phone_home' => null,
                    'phone_mobile' => null,
                    'birthdate' => null,
                    'image' => null,
                    'extras' => null,
                    'is_supervisor' => false,
                    'company' => null,
                    'department' => null,
                    'team' => null,
                    'position' => null,
                    'phone_extension' => null,
                    'reports_to' => null,
                    'fax' => null,
                    'additional_data' => null,
                    'trashed' => null,
                    'name' => 'user second',
                    'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                    'is_admin' => true
                ]
            ],
            'appointment' => '2019-10-29 15:47:16',
            'area_amount' => 25.74,
            'area_unit' => 'm²',
            'assigned_to' => 'user-2',
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47:16',
            'created_by' => 'user-1',
            'date_of_birth' => '1990-01-17',
            'description' => 'Long description goes here',
            'email' => '1@thing.com',
            'file' => null,
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => 7,
            'modified' => '2018-01-18 15:47:16',
            'modified_by' => 'user-1',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => null,
            'primary_thing' => 'Thing #2',
            'rate' => 25.13,
            'salary_amount' => 1000.0,
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'sample_date' => null,
            'test_list' => '',
            'testmetric_amount' => 33.18,
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => 155.22,
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
            'title' => 'Dr',
            'trashed' => null,
            'vip' => true,
            'website' => 'https://google.com',
            'work_start' => '2019-11-19 08:32:00'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->matching('AssignedToUsers')
            ->formatResults(new LabeledFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithPermissions() : void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new \App\ORM\PermissionsFormatter())
            ->formatResults(new LabeledFormatter());

        $keys = array_keys($query->first()->toArray());
        $this->assertTrue(in_array('_permissions', $keys, true));
    }

    public function testFormatResultsWithEmptyRelatedField() : void
    {
        // remove related field value
        $thing = $this->table->get('00000000-0000-0000-0000-000000000001');
        $thing->set('primary_thing', 'a-non-uuid-string');
        $this->table->saveOrFail($thing);

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new LabeledFormatter());

        $this->assertSame('a-non-uuid-string', $query->first()->get('primary_thing'));
    }

    public function testFormatResultsWithNestedRelationAsDisplayField() : void
    {
        // adjust association's target table display-field to a foreign-key
        $this->table->getAssociation('Thingsprimary_thing')
            ->getTarget()
            ->setDisplayField('assigned_to');

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new LabeledFormatter());

        $this->assertSame('user-2', $query->first()->get('primary_thing'));
    }
}
