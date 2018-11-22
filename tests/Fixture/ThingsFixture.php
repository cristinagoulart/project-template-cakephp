<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ThingsFixture extends TestFixture
{
    public $table = 'things';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'created_by' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified_by' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'lookup_created_by' => ['type' => 'index', 'columns' => ['created_by'], 'length' => []],
            'lookup_modified_by' => ['type' => 'index', 'columns' => ['modified_by'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Thing #1',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Thing #2',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'name' => 'Deleted Thing #1',
            'description' => 'Long description goes here',
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => '2018-01-18 15:47:16',
        ],
    ];
}
