<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Plugin;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Alt3\Swagger\Controller\UiController Test Case
 */
class SwaggerUiControllerTest extends IntegrationTestCase
{

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

        $this->get('/swagger');
        $this->assertResponseCode(200);
    }
}
