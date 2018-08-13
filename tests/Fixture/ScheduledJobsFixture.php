<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScheduledJobsFixture
 *
 */
class ScheduledJobsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'job' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'options' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'recurrence' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null],
        'priority' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'start_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'end_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
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
            'name' => 'Test Job',
            'job' => 'CakeShell::App:foobar',
            'options' => '',
            'recurrence' => 'FREQ=MONTHLY',
            'active' => 1,
            'priority' => 100,
            'start_date' => '2018-01-18 09:00:00',
            'end_date' => null,
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Test Job',
            'job' => 'CakeShell::App:foobar',
            'options' => '',
            'recurrence' => 'FREQ=MONTHLY',
            'active' => 1,
            'priority' => 100,
            'start_date' => null,
            'end_date' => null,
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'name' => 'Test Job',
            'job' => 'CakeShell::App:foobar',
            'options' => '',
            'recurrence' => 'FREQ=HOURLY;INTERVAL=1',
            'active' => 1,
            'priority' => 100,
            'start_date' => null,
            'end_date' => null,
            'created' => '2018-01-18 15:47:16',
            'modified' => '2018-01-18 15:47:16',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null
        ],

    ];
}
