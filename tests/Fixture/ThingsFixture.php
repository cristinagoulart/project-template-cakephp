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
            'trashed' => null,
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
            'trashed' => null,
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
            'trashed' => null,
        ],
    ];
}
