<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LogAuditFixture
 *
 */
class LogAuditFixture extends TestFixture
{

    public $import = ['table' => 'log_audit'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 'd8c3ba90-c418-4e58-8cb6-b65c9095a2dc',
            'timestamp' => 1458749378,
            'primary_key' => 'f71a499f-c835-419b-8601-2a62becfa05e',
            'source' => 'Lorem ipsum dolor sit amet',
            'type' => 'read',
            'parent_source' => 'Lorem ipsum dolor sit amet',
            'changed' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'meta' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.'
        ],
    ];
}
