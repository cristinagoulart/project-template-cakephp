<?php
namespace App\Test\TestCase;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;

class AppTest extends TestCase
{
    /**
     * @dataProvider pluginProvider
     */
    public function testLoadedPlugins(string $plugin, $config): void
    {
        if (empty($config)) {
            $this->assertTrue(Plugin::loaded($plugin), "Plugin $plugin is not loaded");
        } else {
            $enabled = false;
            switch (gettype($config)) {
                case 'string':
                    $enabled = Configure::read($config);
                    break;

                case 'array':
                    foreach ($config as $conf) {
                        if (!Configure::read($conf)) {
                            $enabled = false;
                            break;
                        }

                        $enabled = true;
                    }
                    break;
            }

            $message = "Plugin $plugin is not loaded but [" . implode(' or ', (array)$config) . "] is true";
            $this->assertEquals($enabled, Plugin::loaded($plugin), $message);
        }
    }

    /**
     * @return mixed[] Plugins and settings
     */
    public function pluginProvider(): array
    {
        return [
            ['ADmad/JwtAuth', 'API.auth'],
            ['AdminLTE', null],
            ['Alt3/Swagger', ['API.auth', 'Swagger.crawl']],
            ['AuditStash', null],
            ['Burzum/FileStorage', null],
            ['CakeDC/Users', null],
            ['Crud', null],
            ['CsvMigrations', null],
            ['DatabaseLog', null],
            ['Groups', null],
            ['Menu', null],
            ['Migrations', null],
            ['Qobo/Utils', null],
            ['RolesCapabilities', null],
            ['Search', null],
            ['Translations', null],
        ];
    }
}
