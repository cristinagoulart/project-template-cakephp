<?php

namespace App\Test\TestCase\Controller\Api\V1\V0;

use App\Event\Controller\Api\IndexActionListener;
use App\Event\Controller\Api\ViewActionListener;
use App\Feature\Factory;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use Qobo\Utils\TestSuite\JsonIntegrationTestCase;

class ThingsControllerTest extends JsonIntegrationTestCase
{
    public $fixtures = [
        'app.things',
        'app.log_audit',
        'app.users',
        'app.file_storage'
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
        EventManager::instance()->on(new IndexActionListener());
        EventManager::instance()->on(new ViewActionListener());
    }

    public function testFormatPrettyAmount(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/view/00000000-0000-0000-0000-000000000002?format=pretty');
        $response = $this->getParsedResponse();
        $this->assertJsonResponseOk();

        $data = $response->data;
        $this->assertEquals('25.00', $data->area_amount);
    }

    public function testFormatPrettyUnit(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/view/00000000-0000-0000-0000-000000000002?format=pretty');
        $response = $this->getParsedResponse();
        $this->assertJsonResponseOk();

        $data = $response->data;
        $this->assertEquals('m²', $data->area_unit);
    }

    public function testFormatPrettyCurrency(): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/view/00000000-0000-0000-0000-000000000002?format=pretty');
        $response = $this->getParsedResponse();
        $this->assertJsonResponseOk();

        $data = $response->data;
        $this->assertEquals('<span title="Euro">€&nbsp;(EUR)</span>', $data->salary_currency);
    }
}
