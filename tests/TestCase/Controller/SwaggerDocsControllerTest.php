<?php

namespace App\Test\TestCase\Controller;

use Cake\Core\Plugin;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Alt3\Swagger\Controller\DocsController Test Case
 */
class SwaggerDocsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.users',
        'plugin.CsvMigrations.dblists',
        'plugin.CsvMigrations.dblist_items'
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        if (! Plugin::loaded('Alt3/Swagger')) {
            $this->markTestSkipped('Swagger plugin is not loaded.');
        }

        $this->get('/swagger/docs');
        $this->assertResponseCode(200);

        $this->get('/swagger/docs/api');
        $this->assertResponseCode(200);

        $this->assertJson($this->_getBodyAsString());
    }
}
