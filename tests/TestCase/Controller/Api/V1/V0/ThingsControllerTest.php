<?php

namespace App\Test\TestCase\Controller\Api\V1\V0;

use App\Feature\Factory;
use Cake\Utility\Inflector;
use Qobo\Utils\TestSuite\JsonIntegrationTestCase;

class ThingsControllerTest extends JsonIntegrationTestCase
{
    public $fixtures = [
        'app.things'
    ];

    private $moduleName = 'Things';

    public function setUp(): void
    {
        $feature = Factory::get('Module' . DS . 'Things');

        if (! $feature->isActive()) {
            $this->markTestSkipped('Skipping, Things module is disabled');
        }

        parent::setUp();

        $this->setRequestHeaders([], '00000000-0000-0000-0000-000000000002');
    }

    public function testFormatPrettyAmount(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/?format=pretty');
        $response = $this->getParsedResponse();
        $data = $response->data[1];
        $this->assertEquals($data->area_amount, '25.00');
        $this->assertJsonResponseOk();
    }

    public function testFormatPrettyUnit(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '?format=pretty');
        $response = $this->getParsedResponse();
        $data = $response->data[1];
        $this->assertEquals($data->area_unit, 'm²');
        $this->assertJsonResponseOk();
    }

    public function testFormatPrettyCurrency(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '?format=pretty');
        $response = $this->getParsedResponse();
        $data = $response->data[1];
        $this->assertEquals($data->salary_currency, '<span title="Euro">€&nbsp;(EUR)</span>');
        $this->assertJsonResponseOk();
    }
}
