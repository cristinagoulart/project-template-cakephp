<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ThingsFixture extends TestFixture
{
    public $import = ['model' => 'Things'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Thing #1',
            'email' => '1@thing.com',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'gender' => 'm',
            'country' => 'CY',
            'currency' => 'GBP',
            'area_amount' => '25.738',
            'area_unit' => 'm',
            'salary_amount' => '1000',
            'salary_currency' => 'EUR',
            'test_list' => '',
            'testmetric_amount' => '33.178',
            'testmetric_unit' => 'ft',
            'testmoney_amount' => '155.22',
            'testmoney_currency' => 'USD',
            'assigned_to' => '00000000-0000-0000-0000-000000000002',
            'vip' => true,
            'date_of_birth' => '1990-01-17',
            'work_start' => '08:32',
            'website' => 'https://google.com',
            'bio' => 'A blob type',
            'level' => '7',
            'appointment' => '2019-10-29 15:47:16',
            'phone' => '+35725123456',
            'rate' => '25.134',
            'primary_thing' => '00000000-0000-0000-0000-000000000002',
            'title' => 'Dr',
            'language' => 'grc',
            'photos' => '',
            'file' => '',
            'non_searchable' => '',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Thing #2',
            'email' => '2@thing.com',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'area_amount' => '25',
            'area_unit' => 'm',
            'salary_amount' => '1000',
            'salary_currency' => 'EUR',
            'assigned_to' => '00000000-0000-0000-0000-000000000002',
            'primary_thing' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'name' => 'Deleted Thing #1',
            'email' => '3@thing.com',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => '2018-01-18 15:47:16',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'name' => 'Sample Date change',
            'email' => 'sampledate@thing.com',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'sample_date' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ]
    ];
}
