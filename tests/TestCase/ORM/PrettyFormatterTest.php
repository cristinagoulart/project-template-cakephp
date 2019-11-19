<?php

namespace App\Test\TestCase\ORM;

use App\ORM\PrettyFormatter;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PrettyFormatterTest extends TestCase
{
    public $fixtures = [
        'app.Things',
        'app.Users'
    ];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Things');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testFormatResults(): void
    {
        $expected = [
            'appointment' => '2019-10-29 15:47',
            'area_amount' => '25.74',
            'area_unit' => 'm',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'date_of_birth' => '1990-01-17',
            'description' => '<p>Long description goes here</p>' . "\n",
            'email' => '<a href="mailto:1@thing.com" target="_blank">1@thing.com</a>',
            'file' => '',
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => '7',
            'modified' => '2018-01-18 15:47',
            'modified_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => '',
            'primary_thing' => '<a href="/things/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><i class="menu-icon fa fa-user"></i>&nbsp;&nbsp;Thing #2</a>',
            'rate' => '25.13',
            'salary_amount' => '1000',
            'salary_currency' => 'EUR',
            'sample_date' => '',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => 'USD',
            'title' => 'Dr',
            'trashed' => '',
            'vip' => 'Yes',
            'website' => '<a href="https://google.com" target="_blank">https://google.com</a>',
            'work_start' => '08:32'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new PrettyFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithContain(): void
    {
        $expected = [
            'appointment' => '2019-10-29 15:47',
            'area_amount' => '25.74',
            'area_unit' => 'm',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'assigned_to_user' => [
                'id' => '00000000-0000-0000-0000-000000000002',
                'username' => 'user-2',
                'email' => 'user-2@test.com',
                'first_name' => 'user',
                'last_name' => 'second',
                'activation_date' => '6/24/15, 5:33 PM',
                'secret' => 'xxx',
                'secret_verified' => '',
                'tos_date' => '6/24/15, 5:33 PM',
                'active' => '1',
                'is_superuser' => '1',
                'role' => 'admin',
                'created' => '6/24/15, 5:33 PM',
                'modified' => '6/24/15, 5:33 PM',
                'country' => '',
                'initials' => '',
                'gender' => '',
                'phone_office' => '',
                'phone_home' => '',
                'phone_mobile' => '',
                'birthdate' => '',
                'image' => '',
                'extras' => '',
                'is_supervisor' => '',
                'company' => '',
                'department' => '',
                'team' => '',
                'position' => '',
                'phone_extension' => '',
                'reports_to' => '',
                'fax' => '',
                'additional_data' => '',
                'trashed' => '',
                'name' => 'user second',
                'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                'is_admin' => true
            ],
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'date_of_birth' => '1990-01-17',
            'description' => '<p>Long description goes here</p>' . "\n",
            'email' => '<a href="mailto:1@thing.com" target="_blank">1@thing.com</a>',
            'file' => '',
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => '7',
            'modified' => '2018-01-18 15:47',
            'modified_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => '',
            'primary_thing' => '<a href="/things/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><i class="menu-icon fa fa-user"></i>&nbsp;&nbsp;Thing #2</a>',
            'rate' => '25.13',
            'salary_amount' => '1000',
            'salary_currency' => 'EUR',
            'sample_date' => '',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => 'USD',
            'title' => 'Dr',
            'trashed' => '',
            'vip' => 'Yes',
            'website' => '<a href="https://google.com" target="_blank">https://google.com</a>',
            'work_start' => '08:32'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->contain('AssignedToUsers')
            ->formatResults(new PrettyFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithMatching(): void
    {
        $expected = [
            '_matchingData' => [
                'AssignedToUsers' => [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'username' => 'user-2',
                    'email' => 'user-2@test.com',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'activation_date' => '6/24/15, 5:33 PM',
                    'secret' => 'xxx',
                    'secret_verified' => '',
                    'tos_date' => '6/24/15, 5:33 PM',
                    'active' => '1',
                    'is_superuser' => '1',
                    'role' => 'admin',
                    'created' => '6/24/15, 5:33 PM',
                    'modified' => '6/24/15, 5:33 PM',
                    'country' => '',
                    'initials' => '',
                    'gender' => '',
                    'phone_office' => '',
                    'phone_home' => '',
                    'phone_mobile' => '',
                    'birthdate' => '',
                    'image' => '',
                    'extras' => '',
                    'is_supervisor' => '',
                    'company' => '',
                    'department' => '',
                    'team' => '',
                    'position' => '',
                    'phone_extension' => '',
                    'reports_to' => '',
                    'fax' => '',
                    'additional_data' => '',
                    'trashed' => '',
                    'name' => 'user second',
                    'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                    'is_admin' => true
                ]
            ],
            'appointment' => '2019-10-29 15:47',
            'area_amount' => '25.74',
            'area_unit' => 'm',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'bio' => '',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'date_of_birth' => '1990-01-17',
            'description' => '<p>Long description goes here</p>' . "\n",
            'email' => '<a href="mailto:1@thing.com" target="_blank">1@thing.com</a>',
            'file' => '',
            'gender' => 'Male',
            'id' => '00000000-0000-0000-0000-000000000001',
            'language' => 'Ancient Greek',
            'level' => '7',
            'modified' => '2018-01-18 15:47',
            'modified_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'name' => 'Thing #1',
            'non_searchable' => '',
            'phone' => '+35725123456',
            'photos' => '',
            'primary_thing' => '<a href="/things/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><i class="menu-icon fa fa-user"></i>&nbsp;&nbsp;Thing #2</a>',
            'rate' => '25.13',
            'salary_amount' => '1000',
            'salary_currency' => 'EUR',
            'sample_date' => '',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => 'USD',
            'title' => 'Dr',
            'trashed' => '',
            'vip' => 'Yes',
            'website' => '<a href="https://google.com" target="_blank">https://google.com</a>',
            'work_start' => '08:32'
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->matching('AssignedToUsers')
            ->formatResults(new PrettyFormatter());

        $result = $query->first()->toArray();
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testFormatResultsWithPermissions(): void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new \App\ORM\PermissionsFormatter())
            ->formatResults(new PrettyFormatter());

        $keys = array_keys($query->first()->toArray());
        $this->assertTrue(in_array('_permissions', $keys, true));
    }
}
