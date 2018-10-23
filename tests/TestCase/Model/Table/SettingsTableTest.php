<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    public $Settings;

    public $configSettings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.settings'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Settings') ? [] : ['className' => SettingsTable::class];
        $this->Settings = TableRegistry::get('Settings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Settings);

        parent::tearDown();
    }

    public function testUpdateValidationNoErrors()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => $key,
            'value' => '300',
            'type' => 'integer' // dynamic field to pass type to the validator
        ];
        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEmpty($newEntity->getErrors());
    }

    public function testUpdateValidationWithErrors()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => $key,
            'value' => 'wrong value',
            'type' => 'integer' // dynamic field to pass type to the validator
        ];
        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEquals('The provided value is invalid', $newEntity->getErrors()['value']['custom']);
    }

    public function testGetAlias()
    {
        $configSettings = [
         'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name2' => [
                 'alias' => 'FileStorage.defaultImageSize',
                 'type' => 'string',
               ],
             ],
           ],
         ],
        ];

        $alias = Hash::extract($configSettings, '{s}.{s}.{s}.{s}.alias');
        $this->assertEmpty($this->Settings->getAliasDiff($alias));
    }

    public function testGetNewRecord()
    {
        $configSettings = [
         'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name2' => [
                 'alias' => 'FileStorage.defaultImageSize',
                 'type' => 'string',
               ],
             ],
            ],
          ],
          'N Tab1' => [
           'Another Column' => [
             'This section 1' => [
               'name3' => [
                 'alias' => 'Avatar.defaultImage',
                 'type' => 'string',
               ],
             ],
           ],
          ]
        ];

        $alias = Hash::extract($configSettings, '{s}.{s}.{s}.{s}.alias');
        $this->assertEquals(['Avatar.defaultImage'], $this->Settings->getAliasDiff($alias));
    }

    public function testGetException()
    {
        $configSettings = [
         'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name2' => [
                 'alias' => 'FileStorage.defaultImageSize',
                 'type' => 'string',
               ],
             ],
           ],
         ],
          'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name3' => [
                 'alias' => 'Pizza.with.pinapple',
                 'type' => 'string',
               ],
             ],
           ],
          ],
        ];

        $alias = Hash::extract($configSettings, '{s}.{s}.{s}.{s}.alias');
        $this->expectException('\Exception');
        $this->Settings->getAliasDiff($alias);
    }
}
