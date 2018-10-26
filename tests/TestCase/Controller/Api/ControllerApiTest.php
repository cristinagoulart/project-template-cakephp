<?php
namespace App\Test\TestCase\Controller\Api;

use App\Feature\Factory;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\TestSuite\JsonIntegrationTestCase;

class ControllerApiTest extends JsonIntegrationTestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.CsvMigrations.dblists',
        'plugin.CsvMigrations.dblist_items',
        'app.log_audit'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->setRequestHeaders([], '00000000-0000-0000-0000-000000000002');
    }

    public function testApiFilesPlacedCorrectly(): void
    {
        $path = App::path('Controller/Api')[0];
        $dir = new Folder($path);
        $found = 0;

        // checking for scanned files
        foreach ($dir->find('^\w+Controller\.php$') as $file) {
            $found++;
        }

        $this->assertEquals(0, $found, "Check API directory. Not all controllers were moved to corresponding API subdirs");
    }

    /**
     * @dataProvider modulesProvider
     */
    public function testIndex(string $module): void
    {
        $this->get('/api/' . Inflector::dasherize($module));
        $this->assertJsonResponseOk();
    }

    /**
     * @dataProvider modulesProvider
     */
    public function testView(string $module): void
    {
        $table = TableRegistry::getTableLocator()->get($module);
        $entity = $table->newEntity();
        $table->save($entity);

        $this->get('/api/' . Inflector::dasherize($module) . '/view/' . $entity->get($table->getPrimaryKey()));
        $this->assertJsonResponseOk();

        $response = $this->getParsedResponse();
        $this->assertEquals($entity->get($table->getPrimaryKey()), $response->data->{$table->getPrimaryKey()});
    }

    /**
     * @dataProvider modulesProvider
     */
    public function testAdd(string $module): void
    {
        $table = TableRegistry::getTableLocator()->get($module);

        $this->post('/api/' . Inflector::dasherize($module) . '/add/');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [201, 422]));
        $this->assertContentType('application/json');

        if (201 === $this->_response->getStatusCode()) {
            $response = $this->getParsedResponse();
            $this->assertEquals(36, strlen($response->data->{$table->getPrimaryKey()}));
        }
    }

    /**
     * @dataProvider modulesProvider
     */
    public function testEdit(string $module): void
    {
        $table = TableRegistry::getTableLocator()->get($module);
        $entity = $table->newEntity();
        $table->save($entity);

        $this->put('/api/' . Inflector::dasherize($module) . '/edit/' . $entity->get($table->getPrimaryKey()));
        $this->assertJsonResponseOk();

        $response = $this->getParsedResponse();
        $this->assertInternalType('array', $response->data);
        $this->assertEmpty($response->data);
    }

    /**
     * @dataProvider modulesProvider
     */
    public function testDelete(string $module): void
    {
        $table = TableRegistry::getTableLocator()->get($module);
        $entity = $table->newEntity();
        $table->save($entity);

        $this->delete('/api/' . Inflector::dasherize($module) . '/delete/' . $entity->get($table->getPrimaryKey()));
        $this->assertJsonResponseOk();

        $response = $this->getParsedResponse();
        $this->assertInternalType('array', $response->data);

        $query = $table->find()->where([$table->getPrimaryKey() => $entity->get($table->getPrimaryKey())]);
        $this->assertTrue($query->isEmpty());
    }

    /**
     * Modules provider.
     *
     * @return mixed[]
     */
    public function modulesProvider(): array
    {
        // store default path
        $defaultPath = Configure::read('CsvMigrations.modules.path');

        Configure::write('CsvMigrations.modules.path', CONFIG . 'Modules' . DS);

        $modules = [];
        foreach ((new Folder(App::path('Controller/Api/V1/V0')[0]))->find('^\w+Controller\.php$') as $file) {
            array_push($modules, basename($file, 'Controller.php'));
        }

        $modules = array_filter($modules, [$this, 'isModule']);
        $modules = array_filter($modules, [$this, 'isActive']);

        // restore default path
        Configure::write('CsvMigrations.modules.path', $defaultPath);

        return array_map(function ($module) {
            return [$module];
        }, $modules);
    }

    private function isModule(string $name): bool
    {
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $name, null, ['cacheSkip' => true]))->parse();
        $config = json_decode(json_encode($config), true);

        return ! empty($config);
    }

    private function isActive(string $module): bool
    {
        $feature = Factory::get('Module' . DS . $module);

        return $feature->isActive();
    }
}
