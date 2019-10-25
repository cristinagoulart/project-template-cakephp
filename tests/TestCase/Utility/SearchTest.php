<?php
namespace App\Test\TestCase\Utility;

use App\Utility\Search;
use Cake\TestSuite\TestCase;
use Qobo\Utils\Utility\User;
use Search\Model\Entity\SavedSearch;

class SearchTest extends TestCase
{
    public $fixtures = [
        'app.saved_searches',
        'app.things',
        'app.users'
    ];

    public function setUp() : void
    {
        parent::setUp();

        User::setCurrentUser(['is_superuser' => true]);
    }

    public function testGetFilters() : void
    {
        $result = Search::getFilters('Things');

        $expected = [
            'type' => 'string',
            'label' => 'Email',
            'field' => 'AssignedToUsers.email',
            'association' => 'manyToOne',
            'group' => 'Users (Assigned To)'
        ];
        $index = array_search($expected['field'], array_column($result, 'field'));
        $this->assertSame($expected, $result[$index]);

        $expected = [
            'type' => 'string',
            'label' => 'label name',
            'field' => 'Things.name',
            'group' => 'Things'
        ];
        $index = array_search($expected['field'], array_column($result, 'field'));
        $this->assertSame($expected, $result[$index]);
    }

    public function testGetDisplayFields() : void
    {
        $expected = [
            'Things.name',
            'Things.gender',
            'Things.assigned_to',
            'Things.created',
            'Things.modified'
        ];

        $this->assertSame($expected, Search::getDisplayFields('Things'));
    }

    public function testGetChartOptions() : void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things grouped by created date',
            'model' => 'Things',
            'fields' => ['Things.created', 'COUNT(Things.created)'],
            'group_by' => 'Things.created'
        ]);

        $result = Search::getChartOptions($savedSearch);

        $this->assertCount(3, $result);
        $this->assertSame([], array_diff(['pie', 'bar', 'funnelChart'], array_column($result, 'chart')));

        $this->assertSame('funnelChart', $result[0]['chart']);
        $this->assertSame('Things grouped by created date', $result[0]['slug']);
        $this->assertSame(['Created (COUNT)', 'Created'], $result[0]['options']['labels']);
        $this->assertSame(['created'], $result[0]['options']['xkey']);
        $this->assertSame(['created (COUNT)'], $result[0]['options']['ykeys']);
        $this->assertCount(1, $result[0]['options']['dataChart']['data']);
        $this->assertSame(['value' => 2, 'label' => '2018-01-18 15:47'], $result[0]['options']['dataChart']['data'][0]);

        $this->assertSame('pie', $result[1]['chart']);
        $this->assertSame('Things grouped by created date', $result[1]['slug']);
        $this->assertSame(['2018-01-18 15:47'], $result[1]['options']['dataChart']['data']['labels']);
        $this->assertCount(1, $result[1]['options']['dataChart']['data']['datasets']);
        $this->assertSame([2], $result[1]['options']['dataChart']['data']['datasets'][0]['data']);

        $this->assertSame('bar', $result[2]['chart']);
        $this->assertSame('Things grouped by created date', $result[2]['slug']);
        $this->assertSame(['2018-01-18 15:47'], $result[2]['options']['dataChart']['data']['labels']);
        $this->assertCount(1, $result[2]['options']['dataChart']['data']['datasets']);
        $this->assertSame('Created (COUNT)', $result[2]['options']['dataChart']['data']['datasets'][0]['label']);
        $this->assertSame([2], $result[2]['options']['dataChart']['data']['datasets'][0]['data']);
    }

    public function testGetChartOptionsWithoutGroupByOrAggregate() : void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things'
        ]);

        $this->assertSame([], Search::getChartOptions($savedSearch));
    }

    public function testGetChartOptionsWithGroupByButNotAggregate() : void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
            'fields' => ['Things.created'],
            'group_by' => 'Things.created'
        ]);

        $this->assertSame([], Search::getChartOptions($savedSearch));
    }

    public function testGetChartOptionsWithAggregateButNotGroupBy() : void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
            'fields' => ['COUNT(Things.created)']
        ]);

        $result = Search::getChartOptions($savedSearch);

        $this->assertCount(3, $result);
        $this->assertSame([], array_diff(['pie', 'bar', 'funnelChart'], array_column($result, 'chart')));

        $this->assertSame('funnelChart', $result[0]['chart']);
        $this->assertSame('Things NOT grouped by', $result[0]['slug']);
        $this->assertSame(['Created (COUNT)', 'Created'], $result[0]['options']['labels']);
        $this->assertSame(['created'], $result[0]['options']['xkey']);
        $this->assertSame(['created (COUNT)'], $result[0]['options']['ykeys']);
        $this->assertCount(1, $result[0]['options']['dataChart']['data']);
        $this->assertSame(['value' => 2, 'label' => 'Created'], $result[0]['options']['dataChart']['data'][0]);

        $this->assertSame('pie', $result[1]['chart']);
        $this->assertSame('Things NOT grouped by', $result[1]['slug']);
        $this->assertSame(['Created'], $result[1]['options']['dataChart']['data']['labels']);
        $this->assertCount(1, $result[1]['options']['dataChart']['data']['datasets']);
        $this->assertSame([2], $result[1]['options']['dataChart']['data']['datasets'][0]['data']);

        $this->assertSame('bar', $result[2]['chart']);
        $this->assertSame('Things NOT grouped by', $result[2]['slug']);
        $this->assertSame(['Created'], $result[2]['options']['dataChart']['data']['labels']);
        $this->assertCount(1, $result[2]['options']['dataChart']['data']['datasets']);
        $this->assertSame('Created (COUNT)', $result[2]['options']['dataChart']['data']['datasets'][0]['label']);
        $this->assertSame([2], $result[2]['options']['dataChart']['data']['datasets'][0]['data']);
    }
}
