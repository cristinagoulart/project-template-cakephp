<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use Cake\Core\Configure;
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

    public function testCreateEntityNoKey()
    {
        $this->expectException('Cake\Datasource\Exception\RecordNotFoundException');
        $en = $this->Settings->createEntity('invalid.key', '1234', 'integer', 'app', 'app');
    }

    public function testCreateEntityExistRecord()
    {
        $existEntity = $this->Settings->createEntity('FileStorage.defaultImageSize', '1234', 'integer', 'app', 'app');
        $this->assertEquals(false, $existEntity->isNew());
    }

    public function testCreateEntityNew()
    {
        $params = [
            'key' => 'FileStorage.defaultImageSize',
            'value' => '1234',
            'scope' => 'user',
            'context' => 'bb697cd7-c869-491d-8696-805b1af8c08f',
            'type' => 'integer'
        ];

        $entity = $this->Settings->newEntity($params);
        $myEntity = $this->Settings->createEntity('FileStorage.defaultImageSize', '1234', 'integer', 'user', 'bb697cd7-c869-491d-8696-805b1af8c08f');

        $this->assertEquals($entity, $myEntity);
    }

    public function testCreateEntityPatch()
    {
        $params = [
            'key' => 'ScheduledLog.stats.age',
            'value' => 'my NEW value',
            'scope' => 'user',
            'context' => 'bb697cd7-c869-491d-8696-805b1af8c08f',
            'type' => 'integer'
        ];

        $oldEntity = $this->Settings->find('all')->where(['key' => 'ScheduledLog.stats.age'])->first();
        $patchEntity = $this->Settings->patchEntity($oldEntity, $params);
        $myEntity = $this->Settings->createEntity('ScheduledLog.stats.age', 'my NEW value', 'integer', 'user', 'bb697cd7-c869-491d-8696-805b1af8c08f');

        $this->assertEquals($patchEntity, $myEntity);
    }

    public function testFilterSettings()
    {
        $userRoles = ['settings'];

        $configSettings = [
         'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name1' => [
                 'alias' => 'FileStorage.defaultImageSize',
                 'type' => 'string',
                 'scope' => ['Everyone', 'settings', 'anotherRole'],
               ],
               'name2' => [
                 'alias' => 'Avatar.defaultImage',
                 'type' => 'string',
                 'scope' => ['Everyone', 'anotherRole'],
               ],
             ],
           ],
         ],
        ];

        $configSettingsFilter = [
         'N Tab' => [
           'Another Column' => [
             'This section 1' => [
               'name1' => [
                 'alias' => 'FileStorage.defaultImageSize',
                 'type' => 'string',
                 'scope' => ['Everyone', 'settings', 'anotherRole'],
               ],
             ],
           ],
         ],
        ];

        $filterData = $this->Settings->filterSettings($configSettings, $userRoles);
        $this->assertEquals($configSettingsFilter, $filterData);
    }

    public function testFilterDataException()
    {
        $userRoles = ['settings'];
        // Wrong configuration
        $configSettings = [
         'N Tab' => [
           'This section 1' => [
             'name1' => [
               'alias' => 'FileStorage.defaultImageSize',
               'type' => 'string',
               'scope' => ['Everyone', 'settings', 'anotherRole'],
             ],
           ],
         ],
        ];

        $this->expectException('\RuntimeException');
        $filterData = $this->Settings->filterSettings($configSettings, $userRoles);
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

    public function testContextValidationValidUser()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => 'FileStorage.defaultImageSize',
            'value' => '1234',
            'scope' => 'user',
            'context' => 'bb697cd7-c869-491d-8696-805b1af8c08f',
            'type' => 'integer'
        ];

        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEquals([], $newEntity->getErrors());
    }

    public function testContextValidationErrorUser()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => 'FileStorage.defaultImageSize',
            'value' => '1234',
            'scope' => 'user',
            'context' => 'not a UUID !',
            'type' => 'integer'
        ];

        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEquals('The provided value is invalid', $newEntity->getErrors()['context']['custom']);
    }

    public function testContextValidationValidApp()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => 'FileStorage.defaultImageSize',
            'value' => '1234',
            'scope' => 'app',
            'context' => 'app',
            'type' => 'integer'
        ];

        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEquals([], $newEntity->getErrors());
    }

    public function testContextValidationErrorApp()
    {
        $key = 'FileStorage.defaultImageSize';
        $entity = TableRegistry::get('Settings')->findByKey($key)->first();
        $params = [
            'key' => 'FileStorage.defaultImageSize',
            'value' => '1234',
            'scope' => 'app',
            'context' => 'not app string',
            'type' => 'integer'
        ];

        $newEntity = $this->Settings->patchEntity($entity, $params);
        $this->assertEquals('The provided value is invalid', $newEntity->getErrors()['context']['custom']);
    }
}
