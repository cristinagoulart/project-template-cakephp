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
     * Test display method
     *
     * @return void
     */
    public function testIndex()
    {
        if (! Plugin::loaded('Alt3/Swagger')) {
            return;
        }

        $this->get('/swagger');
        $this->assertResponseCode(200);
    }
}
