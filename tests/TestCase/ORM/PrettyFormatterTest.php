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
            'area_unit' => 'm²',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'bio' => 'A blob type',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'currency' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>',
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
            'salary_amount' => '1,000.00',
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
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

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    public function testFormatResultsWithContain(): void
    {
        $expected = [
            'appointment' => '2019-10-29 15:47',
            'area_amount' => '25.74',
            'area_unit' => 'm²',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'assigned_to_user' => [
                'activation_date' => '6/24/15, 5:33 PM',
                'active' => '1',
                'additional_data' => '',
                'birthdate' => '',
                'company' => '',
                'country' => '',
                'created' => '6/24/15, 5:33 PM',
                'department' => '',
                'email' => 'user-2@test.com',
                'extras' => '',
                'fax' => '',
                'first_name' => 'user',
                'gender' => '',
                'id' => '00000000-0000-0000-0000-000000000002',
                'image' => '',
                'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                'initials' => '',
                'is_admin' => true,
                'is_superuser' => '1',
                'is_supervisor' => '',
                'last_name' => 'second',
                'modified' => '6/24/15, 5:33 PM',
                'name' => 'user second',
                'phone_extension' => '',
                'phone_home' => '',
                'phone_mobile' => '',
                'phone_office' => '',
                'position' => '',
                'reports_to' => '',
                'role' => 'admin',
                'secret' => 'xxx',
                'secret_verified' => '',
                'team' => '',
                'tos_date' => '6/24/15, 5:33 PM',
                'trashed' => '',
                'username' => 'user-2'
            ],
            'bio' => 'A blob type',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'currency' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>',
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
            'salary_amount' => '1,000.00',
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
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
        ksort($result['assigned_to_user']);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    public function testFormatResultsWithMatching(): void
    {
        $expected = [
            '_matchingData' => [
                'AssignedToUsers' => [
                    'activation_date' => '6/24/15, 5:33 PM',
                    'active' => '1',
                    'additional_data' => '',
                    'birthdate' => '',
                    'company' => '',
                    'country' => '',
                    'created' => '6/24/15, 5:33 PM',
                    'department' => '',
                    'email' => 'user-2@test.com',
                    'extras' => '',
                    'fax' => '',
                    'first_name' => 'user',
                    'gender' => '',
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'image' => '',
                    'image_src' => '/uploads/avatars/00000000-0000-0000-0000-000000000002.png',
                    'initials' => '',
                    'is_admin' => true,
                    'is_superuser' => '1',
                    'is_supervisor' => '',
                    'last_name' => 'second',
                    'modified' => '6/24/15, 5:33 PM',
                    'name' => 'user second',
                    'phone_extension' => '',
                    'phone_home' => '',
                    'phone_mobile' => '',
                    'phone_office' => '',
                    'position' => '',
                    'reports_to' => '',
                    'role' => 'admin',
                    'secret' => 'xxx',
                    'secret_verified' => '',
                    'team' => '',
                    'tos_date' => '6/24/15, 5:33 PM',
                    'trashed' => '',
                    'username' => 'user-2'
                ]
            ],
            'appointment' => '2019-10-29 15:47',
            'area_amount' => '25.74',
            'area_unit' => 'm²',
            'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-2</a>',
            'bio' => 'A blob type',
            'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
            'created' => '2018-01-18 15:47',
            'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user-1</a>',
            'currency' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>',
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
            'salary_amount' => '1,000.00',
            'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
            'test_list' => '',
            'testmetric_amount' => '33.18',
            'testmetric_unit' => 'ft²',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
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
        ksort($result['_matchingData']['AssignedToUsers']);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
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
