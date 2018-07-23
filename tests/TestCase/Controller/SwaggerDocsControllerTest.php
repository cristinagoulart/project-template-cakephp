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
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        if (! Plugin::loaded('Alt3/Swagger')) {
            return;
        }

        $this->get('/swagger/docs');
        $this->assertResponseCode(200);

        $this->get('/swagger/docs/api');
        $this->assertResponseCode(200);

        $this->assertJson($this->_getBodyAsString());
    }
}
