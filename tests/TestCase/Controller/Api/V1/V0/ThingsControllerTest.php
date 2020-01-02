<?php

namespace App\Test\TestCase\Controller\Api\V1\V0;

use App\Event\Controller\Api\IndexActionListener;
use App\Event\Controller\Api\RelatedActionListener;
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
        'app.file_storage',
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
        EventManager::instance()->on(new RelatedActionListener());
    }

    /**
     * @param mixed[] $expected
     * @dataProvider rawDataProvider
     */
    public function testIndexRaw(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '?sort=id&direction=asc');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data[0]), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @param mixed[] $expected
     * @dataProvider prettifiedDataProvider
     */
    public function testIndexPrettified(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '?format=pretty&sort=id&direction=asc');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data[0]), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @param mixed[] $expected
     * @dataProvider rawDataProvider
     */
    public function testViewRaw(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/view/00000000-0000-0000-0000-000000000001');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @param mixed[] $expected
     * @dataProvider prettifiedDataProvider
     */
    public function testViewPrettified(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/view/00000000-0000-0000-0000-000000000001?format=pretty');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @param mixed[] $expected
     * @dataProvider rawDataProvider
     */
    public function testRelatedRaw(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/related/00000000-0000-0000-0000-000000000002/Thingsprimary_thing');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data[0]), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @param mixed[] $expected
     * @dataProvider prettifiedDataProvider
     */
    public function testRelatedPrettified(array $expected): void
    {
        $this->get('/api/' . Inflector::dasherize($this->moduleName) . '/related/00000000-0000-0000-0000-000000000002/Thingsprimary_thing?format=pretty');
        $this->assertJsonResponseOk();
        $response = $this->getParsedResponse();

        $result = json_decode((string)json_encode($response->data[0]), true);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @return mixed[]
     */
    public function rawDataProvider(): array
    {
        return [
            [
                [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'name' => 'Thing #1',
                    'description' => 'Long description goes here',
                    'test_list' => 'first.second_children',
                    'testmetric_amount' => 33.18,
                    'testmetric_unit' => 'ft',
                    'testmoney_amount' => 155.22,
                    'testmoney_currency' => 'USD',
                    'created' => '2018-01-18T15:47:16+00:00',
                    'modified' => '2018-01-18T15:47:16+00:00',
                    'created_by' => '00000000-0000-0000-0000-000000000001',
                    'modified_by' => '00000000-0000-0000-0000-000000000001',
                    'trashed' => null,
                    'country' => 'CY',
                    'gender' => 'm',
                    'assigned_to' => '00000000-0000-0000-0000-000000000002',
                    'salary_amount' => 1000,
                    'salary_currency' => 'EUR',
                    'vip' => true,
                    'area_amount' => 25.74,
                    'area_unit' => 'm',
                    'date_of_birth' => '1990-01-17',
                    'work_start' => '08:32',
                    'website' => 'https://google.com',
                    'bio' => 'A blob type',
                    'level' => 7,
                    'appointment' => '2019-10-29T15:47:16+00:00',
                    'phone' => '+35725123456',
                    'email' => '1@thing.com',
                    'rate' => 25.13,
                    'primary_thing' => '00000000-0000-0000-0000-000000000002',
                    'title' => 'Dr',
                    'language' => 'grc',
                    'photos' => [],
                    'file' => [],
                    'non_searchable' => '',
                    'sample_date' => null,
                    'currency' => 'GBP',
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function prettifiedDataProvider(): array
    {
        return [
            [
                [
                    'appointment' => '2019-10-29 15:47',
                    'area_amount' => '25.74',
                    'area_unit' => 'm²',
                    'assigned_to' => '<a href="/users/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000002.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;user second</a>',
                    'bio' => 'A blob type',
                    'country' => '<span class="flag-icon flag-icon-cy flag-icon-default"></span>&nbsp;&nbsp;Cyprus',
                    'created' => '2018-01-18 15:47',
                    'created_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;first1 last1</a>',
                    'currency' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>',
                    'date_of_birth' => '1990-01-17',
                    'description' => '<p>Long description goes here</p>' . "\n",
                    'email' => '<a href="mailto:1@thing.com" target="_blank">1@thing.com</a>',
                    'file' => '',
                    'gender' => 'Male',
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'language' => 'Ancient Greek',
                    'level' => '7',
                    'modified' => '2018-01-18 15:47',
                    'modified_by' => '<a href="/users/view/00000000-0000-0000-0000-000000000001" class="btn btn-primary btn-xs"><img alt="Thumbnail" src="/uploads/avatars/00000000-0000-0000-0000-000000000001.png" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;first1 last1</a>',
                    'name' => 'Thing #1',
                    'non_searchable' => '',
                    'phone' => '+35725123456',
                    'photos' => '',
                    'primary_thing' => '<a href="/things/view/00000000-0000-0000-0000-000000000002" class="btn btn-primary btn-xs"><i class="menu-icon fa fa-user"></i>&nbsp;&nbsp;Thing #2</a>',
                    'rate' => '25.13',
                    'salary_amount' => '1,000.00',
                    'salary_currency' => '<span title="Euro">€&nbsp;(EUR)</span>',
                    'sample_date' => '',
                    'test_list' => 'first - second children',
                    'testmetric_amount' => '33.18',
                    'testmetric_unit' => 'ft²',
                    'testmoney_amount' => '155.22',
                    'testmoney_currency' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>',
                    'title' => 'Dr',
                    'trashed' => '',
                    'vip' => 'Yes',
                    'website' => '<a href="https://google.com" target="_blank">https://google.com</a>',
                    'work_start' => '08:32',
                ],
            ],
        ];
    }
}
