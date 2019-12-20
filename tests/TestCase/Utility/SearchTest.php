<?php

namespace App\Test\TestCase\Utility;

use App\Utility\Search;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Model\Entity\SavedSearch;

class SearchTest extends TestCase
{
    public $fixtures = [
        'app.saved_searches',
        'app.things',
        'app.users',
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CsvMigrations.dblists',
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param string $modelName
     * @param mixed[] $expected
     * @dataProvider filtersProvider
     */
    public function testGetFilters(string $modelName, array $expected): void
    {
        $timeStart = microtime(true);
        $result = Search::getFilters($modelName);
        $firstCallTiming = microtime(true) - $timeStart;

        usort($result, function (array $a, array $b) {
            return strcmp($a['field'], $b['field']);
        });

        foreach ($expected as $key => $value) {
            $key = array_search($value['field'], array_column($result, 'field'), true);
            unset($result[$key]['options']);
            $this->assertSame($value, $result[$key]);
        }

        $timeStart = microtime(true);
        Search::getFilters($modelName);
        $secondCallTiming = microtime(true) - $timeStart;

        $this->assertTrue($firstCallTiming > $secondCallTiming, 'In-memory caching is broken');
    }

    public function testGetDisplayFieldsFromView(): void
    {
        $expected = [
            'Things.name',
            'Things.gender',
            'Things.assigned_to',
            'Things.created',
            'Things.modified',
        ];

        $this->assertSame($expected, Search::getDisplayFields('Things'));
    }

    public function testGetDisplayFieldsFromSystemSearch(): void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        $table->deleteAll([]);

        $expected = ['Things.name'];

        // create system search
        $table->saveOrFail(
            $table->newEntity([
                'name' => 'A name',
                'model' => 'Things',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'system' => true,
                'fields' => $expected,
            ])
        );

        $this->assertSame($expected, Search::getDisplayFields('Things'));
    }

    public function testGetDisplayFieldsWithDisplayFieldBeingTheFirstFilter(): void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        $table->deleteAll([]);

        $expected = ['AssignedToUsers.activation_date'];
        $this->assertSame(
            current($expected),
            Search::getFilters('Things')[0]['field'],
            'Pre-test assertion, if $expected does not match filters first field, adjust $expected accordingly'
        );

        $table->saveOrFail(
            $table->newEntity([
                'name' => 'A name',
                'model' => 'Things',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'system' => true,
                'fields' => $expected,
            ])
        );

        $this->assertSame($expected, Search::getDisplayFields('Things'));
    }

    public function testGetDisplayFieldsFromDatabaseColumns(): void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        $table->deleteAll([]);

        $expected = [
            'SavedSearches.conjunction',
            'SavedSearches.group_by',
            'SavedSearches.model',
            'SavedSearches.name',
            'SavedSearches.order_by_direction',
            'SavedSearches.order_by_field',
        ];

        $displayFields = Search::getDisplayFields('Search.SavedSearches');
        sort($displayFields);

        $this->assertSame($expected, $displayFields);
    }

    /**
     * @param mixed[] $expected
     * @dataProvider chartOptionsProvider
     */
    public function testGetChartOptions(array $expected): void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things grouped by created date',
            'model' => 'Things',
            'fields' => ['Things.created', 'COUNT(Things.created)'],
            'group_by' => 'Things.created',
        ]);

        $result = Search::getChartOptions($savedSearch);

        foreach ($expected as $key => $value) {
            // id is dynamic
            unset($result[$key]['id']);
            $this->assertSame($value, $result[$key]);
        }
    }

    public function testGetChartOptionsWithoutGroupByOrAggregate(): void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
        ]);

        $this->assertSame([], Search::getChartOptions($savedSearch));
    }

    public function testGetChartOptionsWithGroupByButNotAggregate(): void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
            'fields' => ['Things.created'],
            'group_by' => 'Things.created',
        ]);

        $this->assertSame([], Search::getChartOptions($savedSearch));
    }

    /**
     * @param mixed[] $expected
     * @dataProvider chartOptionsWithAggregateProvider
     */
    public function testGetChartOptionsWithAggregateButNotGroupBy(array $expected): void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
            'fields' => ['COUNT(Things.created)'],
        ]);

        $result = Search::getChartOptions($savedSearch);

        foreach ($expected as $key => $value) {
            // id is dynamic
            unset($result[$key]['id']);
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @return mixed[]
     */
    public function chartOptionsProvider(): array
    {
        return [
            [
                [
                    ['icon' => 'filter', 'chart' => 'funnelChart', 'slug' => 'Things grouped by created date', 'options' => ['resize' => true, 'hideHover' => true, 'labels' => ['Created (COUNT)', 'Created'], 'xkey' => ['created'], 'ykeys' => ['created (COUNT)'], 'dataChart' => ['type' => 'funnelChart', 'data' => [['value' => 3, 'label' => '2018-01-18 15:47']]]]],
                    ['icon' => 'pie-chart', 'chart' => 'pie', 'slug' => 'Things grouped by created date', 'options' => ['resize' => true, 'hideHover' => true, 'dataChart' => ['type' => 'pie', 'data' => ['labels' => ['2018-01-18 15:47'], 'datasets' => [['backgroundColor' => ['#c7004c'], 'data' => [3]]]]]]],
                    ['icon' => 'bar-chart', 'chart' => 'bar', 'slug' => 'Things grouped by created date', 'options' => ['resize' => true, 'hideHover' => true, 'dataChart' => ['type' => 'bar', 'data' => ['labels' => ['2018-01-18 15:47'], 'datasets' => [['label' => 'Created (COUNT)', 'backgroundColor' => ['#948412'], 'data' => [3]]]], 'options' => ['legend' => ['display' => false], 'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true]]], 'xAxes' => [['ticks' => ['autoSkip' => false]]]]]]]],
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function chartOptionsWithAggregateProvider(): array
    {
        return [
            [
                [
                    ['icon' => 'filter', 'chart' => 'funnelChart', 'slug' => 'Things NOT grouped by', 'options' => ['resize' => true, 'hideHover' => true, 'labels' => ['Created (COUNT)', 'Created'], 'xkey' => ['created'], 'ykeys' => ['created (COUNT)'], 'dataChart' => ['type' => 'funnelChart', 'data' => [['value' => 3, 'label' => 'Created']]]]],
                    ['icon' => 'pie-chart', 'chart' => 'pie', 'slug' => 'Things NOT grouped by', 'options' => ['resize' => true, 'hideHover' => true, 'dataChart' => ['type' => 'pie', 'data' => ['labels' => ['Created'], 'datasets' => [['backgroundColor' => ['#a06ee1'], 'data' => [3]]]]]]],
                    ['icon' => 'bar-chart', 'chart' => 'bar', 'slug' => 'Things NOT grouped by', 'options' => ['resize' => true, 'hideHover' => true, 'dataChart' => ['type' => 'bar', 'data' => ['labels' => ['Created'], 'datasets' => [['label' => 'Created (COUNT)', 'backgroundColor' => ['#5460a5'], 'data' => [3]]]], 'options' => ['legend' => ['display' => false], 'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true]]], 'xAxes' => [['ticks' => ['autoSkip' => false]]]]]]]],
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function filtersProvider(): array
    {
        return [
            [
                'Things',
                [
                    ['type' => 'datetime', 'label' => 'Activation Date', 'field' => 'AssignedToUsers.activation_date', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'boolean', 'label' => 'Active', 'field' => 'AssignedToUsers.active', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'text', 'label' => 'Additional Data', 'field' => 'AssignedToUsers.additional_data', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Api Token', 'field' => 'AssignedToUsers.api_token', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'date', 'label' => 'Birthdate', 'field' => 'AssignedToUsers.birthdate', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Company', 'field' => 'AssignedToUsers.company', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Country', 'field' => 'AssignedToUsers.country', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'AssignedToUsers.created', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Department', 'field' => 'AssignedToUsers.department', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Email', 'field' => 'AssignedToUsers.email', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'text', 'label' => 'Extras', 'field' => 'AssignedToUsers.extras', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Fax', 'field' => 'AssignedToUsers.fax', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'First Name', 'field' => 'AssignedToUsers.first_name', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Gender', 'field' => 'AssignedToUsers.gender', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Initials', 'field' => 'AssignedToUsers.initials', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'boolean', 'label' => 'Is Superuser', 'field' => 'AssignedToUsers.is_superuser', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'boolean', 'label' => 'Is Supervisor', 'field' => 'AssignedToUsers.is_supervisor', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Last Name', 'field' => 'AssignedToUsers.last_name', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'AssignedToUsers.modified', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Password', 'field' => 'AssignedToUsers.password', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Phone Extension', 'field' => 'AssignedToUsers.phone_extension', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Phone Home', 'field' => 'AssignedToUsers.phone_home', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Phone Mobile', 'field' => 'AssignedToUsers.phone_mobile', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Phone Office', 'field' => 'AssignedToUsers.phone_office', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Position', 'field' => 'AssignedToUsers.position', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Role', 'field' => 'AssignedToUsers.role', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Secret', 'field' => 'AssignedToUsers.secret', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'boolean', 'label' => 'Secret Verified', 'field' => 'AssignedToUsers.secret_verified', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Team', 'field' => 'AssignedToUsers.team', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Token', 'field' => 'AssignedToUsers.token', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Token Expires', 'field' => 'AssignedToUsers.token_expires', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Tos Date', 'field' => 'AssignedToUsers.tos_date', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'string', 'label' => 'Username', 'field' => 'AssignedToUsers.username', 'association' => 'manyToOne', 'group' => 'Users (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Activation Date', 'field' => 'CreatedByUsers.activation_date', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'boolean', 'label' => 'Active', 'field' => 'CreatedByUsers.active', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'text', 'label' => 'Additional Data', 'field' => 'CreatedByUsers.additional_data', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Api Token', 'field' => 'CreatedByUsers.api_token', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'date', 'label' => 'Birthdate', 'field' => 'CreatedByUsers.birthdate', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Company', 'field' => 'CreatedByUsers.company', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Country', 'field' => 'CreatedByUsers.country', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'CreatedByUsers.created', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Department', 'field' => 'CreatedByUsers.department', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Email', 'field' => 'CreatedByUsers.email', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'text', 'label' => 'Extras', 'field' => 'CreatedByUsers.extras', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Fax', 'field' => 'CreatedByUsers.fax', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'First Name', 'field' => 'CreatedByUsers.first_name', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Gender', 'field' => 'CreatedByUsers.gender', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Initials', 'field' => 'CreatedByUsers.initials', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'boolean', 'label' => 'Is Superuser', 'field' => 'CreatedByUsers.is_superuser', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'boolean', 'label' => 'Is Supervisor', 'field' => 'CreatedByUsers.is_supervisor', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Last Name', 'field' => 'CreatedByUsers.last_name', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'CreatedByUsers.modified', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Password', 'field' => 'CreatedByUsers.password', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Phone Extension', 'field' => 'CreatedByUsers.phone_extension', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Phone Home', 'field' => 'CreatedByUsers.phone_home', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Phone Mobile', 'field' => 'CreatedByUsers.phone_mobile', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Phone Office', 'field' => 'CreatedByUsers.phone_office', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Position', 'field' => 'CreatedByUsers.position', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Role', 'field' => 'CreatedByUsers.role', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Secret', 'field' => 'CreatedByUsers.secret', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'boolean', 'label' => 'Secret Verified', 'field' => 'CreatedByUsers.secret_verified', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Team', 'field' => 'CreatedByUsers.team', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Token', 'field' => 'CreatedByUsers.token', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'datetime', 'label' => 'Token Expires', 'field' => 'CreatedByUsers.token_expires', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'datetime', 'label' => 'Tos Date', 'field' => 'CreatedByUsers.tos_date', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'string', 'label' => 'Username', 'field' => 'CreatedByUsers.username', 'association' => 'manyToOne', 'group' => 'Users (Created By)'],
                    ['type' => 'datetime', 'label' => 'Activation Date', 'field' => 'ModifiedByUsers.activation_date', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Active', 'field' => 'ModifiedByUsers.active', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'text', 'label' => 'Additional Data', 'field' => 'ModifiedByUsers.additional_data', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Api Token', 'field' => 'ModifiedByUsers.api_token', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'date', 'label' => 'Birthdate', 'field' => 'ModifiedByUsers.birthdate', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Company', 'field' => 'ModifiedByUsers.company', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Country', 'field' => 'ModifiedByUsers.country', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'ModifiedByUsers.created', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Department', 'field' => 'ModifiedByUsers.department', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Email', 'field' => 'ModifiedByUsers.email', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'text', 'label' => 'Extras', 'field' => 'ModifiedByUsers.extras', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Fax', 'field' => 'ModifiedByUsers.fax', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'First Name', 'field' => 'ModifiedByUsers.first_name', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Gender', 'field' => 'ModifiedByUsers.gender', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Initials', 'field' => 'ModifiedByUsers.initials', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Is Superuser', 'field' => 'ModifiedByUsers.is_superuser', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Is Supervisor', 'field' => 'ModifiedByUsers.is_supervisor', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Last Name', 'field' => 'ModifiedByUsers.last_name', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'ModifiedByUsers.modified', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Password', 'field' => 'ModifiedByUsers.password', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Phone Extension', 'field' => 'ModifiedByUsers.phone_extension', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Phone Home', 'field' => 'ModifiedByUsers.phone_home', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Phone Mobile', 'field' => 'ModifiedByUsers.phone_mobile', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Phone Office', 'field' => 'ModifiedByUsers.phone_office', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Position', 'field' => 'ModifiedByUsers.position', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Role', 'field' => 'ModifiedByUsers.role', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Secret', 'field' => 'ModifiedByUsers.secret', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Secret Verified', 'field' => 'ModifiedByUsers.secret_verified', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Team', 'field' => 'ModifiedByUsers.team', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Token', 'field' => 'ModifiedByUsers.token', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Token Expires', 'field' => 'ModifiedByUsers.token_expires', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Tos Date', 'field' => 'ModifiedByUsers.tos_date', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'string', 'label' => 'Username', 'field' => 'ModifiedByUsers.username', 'association' => 'manyToOne', 'group' => 'Users (Modified By)'],
                    ['type' => 'reminder', 'label' => 'Appointment', 'field' => 'Things.appointment', 'group' => 'Things'],
                    ['type' => 'decimal', 'label' => 'Area Amount', 'field' => 'Things.area_amount', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Area Unit', 'field' => 'Things.area_unit', 'group' => 'Things'],
                    ['type' => 'related', 'label' => 'Assigned To', 'display_field' => 'name', 'source' => 'users', 'field' => 'Things.assigned_to', 'group' => 'Things'],
                    ['type' => 'blob', 'label' => 'Bio', 'field' => 'Things.bio', 'group' => 'Things'],
                    ['type' => 'country', 'label' => 'Country', 'field' => 'Things.country', 'group' => 'Things'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'Things.created', 'group' => 'Things'],
                    ['type' => 'related', 'label' => 'Created By', 'display_field' => 'name', 'source' => 'users', 'field' => 'Things.created_by', 'group' => 'Things'],
                    ['type' => 'currency', 'label' => 'Currency', 'field' => 'Things.currency', 'group' => 'Things'],
                    ['type' => 'date', 'label' => 'Date Of Birth', 'field' => 'Things.date_of_birth', 'group' => 'Things'],
                    ['type' => 'text', 'label' => 'label description', 'field' => 'Things.description', 'group' => 'Things'],
                    ['type' => 'email', 'label' => 'Email', 'field' => 'Things.email', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Gender', 'field' => 'Things.gender', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Language', 'field' => 'Things.language', 'group' => 'Things'],
                    ['type' => 'integer', 'label' => 'Level', 'field' => 'Things.level', 'group' => 'Things'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'Things.modified', 'group' => 'Things'],
                    ['type' => 'related', 'label' => 'Modified By', 'display_field' => 'name', 'source' => 'users', 'field' => 'Things.modified_by', 'group' => 'Things'],
                    ['type' => 'string', 'label' => 'label name', 'field' => 'Things.name', 'group' => 'Things'],
                    ['type' => 'phone', 'label' => 'Phone', 'field' => 'Things.phone', 'group' => 'Things'],
                    ['type' => 'related', 'label' => 'Primary Thing', 'display_field' => 'name', 'source' => 'things', 'field' => 'Things.primary_thing', 'group' => 'Things'],
                    ['type' => 'decimal', 'label' => 'Rate', 'field' => 'Things.rate', 'group' => 'Things'],
                    ['type' => 'decimal', 'label' => 'Salary Amount', 'field' => 'Things.salary_amount', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Salary Currency', 'field' => 'Things.salary_currency', 'group' => 'Things'],
                    ['type' => 'datetime', 'label' => 'Sample Date', 'field' => 'Things.sample_date', 'group' => 'Things'],
                    ['type' => 'sublist', 'label' => 'Test List', 'field' => 'Things.test_list', 'group' => 'Things'],
                    ['type' => 'decimal', 'label' => 'Testmetric Amount', 'field' => 'Things.testmetric_amount', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Testmetric Unit', 'field' => 'Things.testmetric_unit', 'group' => 'Things'],
                    ['type' => 'decimal', 'label' => 'Testmoney Amount', 'field' => 'Things.testmoney_amount', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Testmoney Currency', 'field' => 'Things.testmoney_currency', 'group' => 'Things'],
                    ['type' => 'list', 'label' => 'Title', 'field' => 'Things.title', 'group' => 'Things'],
                    ['type' => 'boolean', 'label' => 'Vip', 'field' => 'Things.vip', 'group' => 'Things'],
                    ['type' => 'url', 'label' => 'Website', 'field' => 'Things.website', 'group' => 'Things'],
                    ['type' => 'time', 'label' => 'Work Start', 'field' => 'Things.work_start', 'group' => 'Things'],
                ],
            ],
            [
                'Users',
                [
                    ['type' => 'reminder', 'label' => 'Appointment', 'field' => 'AssignedToThings.appointment', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'decimal', 'label' => 'Area Amount', 'field' => 'AssignedToThings.area_amount', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Area Unit', 'field' => 'AssignedToThings.area_unit', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'related', 'label' => 'Assigned To', 'display_field' => 'name', 'source' => 'users', 'field' => 'AssignedToThings.assigned_to', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'blob', 'label' => 'Bio', 'field' => 'AssignedToThings.bio', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'country', 'label' => 'Country', 'field' => 'AssignedToThings.country', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'AssignedToThings.created', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'related', 'label' => 'Created By', 'display_field' => 'name', 'source' => 'users', 'field' => 'AssignedToThings.created_by', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'currency', 'label' => 'Currency', 'field' => 'AssignedToThings.currency', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'date', 'label' => 'Date Of Birth', 'field' => 'AssignedToThings.date_of_birth', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'text', 'label' => 'label description', 'field' => 'AssignedToThings.description', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'email', 'label' => 'Email', 'field' => 'AssignedToThings.email', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Gender', 'field' => 'AssignedToThings.gender', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Language', 'field' => 'AssignedToThings.language', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'integer', 'label' => 'Level', 'field' => 'AssignedToThings.level', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'AssignedToThings.modified', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'related', 'label' => 'Modified By', 'display_field' => 'name', 'source' => 'users', 'field' => 'AssignedToThings.modified_by', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'string', 'label' => 'label name', 'field' => 'AssignedToThings.name', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'phone', 'label' => 'Phone', 'field' => 'AssignedToThings.phone', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'related', 'label' => 'Primary Thing', 'display_field' => 'name', 'source' => 'things', 'field' => 'AssignedToThings.primary_thing', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'decimal', 'label' => 'Rate', 'field' => 'AssignedToThings.rate', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'decimal', 'label' => 'Salary Amount', 'field' => 'AssignedToThings.salary_amount', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Salary Currency', 'field' => 'AssignedToThings.salary_currency', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'datetime', 'label' => 'Sample Date', 'field' => 'AssignedToThings.sample_date', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'sublist', 'label' => 'Test List', 'field' => 'AssignedToThings.test_list', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'decimal', 'label' => 'Testmetric Amount', 'field' => 'AssignedToThings.testmetric_amount', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Testmetric Unit', 'field' => 'AssignedToThings.testmetric_unit', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'decimal', 'label' => 'Testmoney Amount', 'field' => 'AssignedToThings.testmoney_amount', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Testmoney Currency', 'field' => 'AssignedToThings.testmoney_currency', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'list', 'label' => 'Title', 'field' => 'AssignedToThings.title', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'boolean', 'label' => 'Vip', 'field' => 'AssignedToThings.vip', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'url', 'label' => 'Website', 'field' => 'AssignedToThings.website', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'time', 'label' => 'Work Start', 'field' => 'AssignedToThings.work_start', 'association' => 'oneToMany', 'group' => 'Things (Assigned To)'],
                    ['type' => 'reminder', 'label' => 'Appointment', 'field' => 'CreatedByThings.appointment', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'decimal', 'label' => 'Area Amount', 'field' => 'CreatedByThings.area_amount', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Area Unit', 'field' => 'CreatedByThings.area_unit', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'related', 'label' => 'Assigned To', 'display_field' => 'name', 'source' => 'users', 'field' => 'CreatedByThings.assigned_to', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'blob', 'label' => 'Bio', 'field' => 'CreatedByThings.bio', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'country', 'label' => 'Country', 'field' => 'CreatedByThings.country', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'CreatedByThings.created', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'related', 'label' => 'Created By', 'display_field' => 'name', 'source' => 'users', 'field' => 'CreatedByThings.created_by', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'currency', 'label' => 'Currency', 'field' => 'CreatedByThings.currency', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'date', 'label' => 'Date Of Birth', 'field' => 'CreatedByThings.date_of_birth', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'text', 'label' => 'label description', 'field' => 'CreatedByThings.description', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'email', 'label' => 'Email', 'field' => 'CreatedByThings.email', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Gender', 'field' => 'CreatedByThings.gender', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Language', 'field' => 'CreatedByThings.language', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'integer', 'label' => 'Level', 'field' => 'CreatedByThings.level', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'CreatedByThings.modified', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'related', 'label' => 'Modified By', 'display_field' => 'name', 'source' => 'users', 'field' => 'CreatedByThings.modified_by', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'string', 'label' => 'label name', 'field' => 'CreatedByThings.name', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'phone', 'label' => 'Phone', 'field' => 'CreatedByThings.phone', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'related', 'label' => 'Primary Thing', 'display_field' => 'name', 'source' => 'things', 'field' => 'CreatedByThings.primary_thing', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'decimal', 'label' => 'Rate', 'field' => 'CreatedByThings.rate', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'decimal', 'label' => 'Salary Amount', 'field' => 'CreatedByThings.salary_amount', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Salary Currency', 'field' => 'CreatedByThings.salary_currency', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'datetime', 'label' => 'Sample Date', 'field' => 'CreatedByThings.sample_date', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'sublist', 'label' => 'Test List', 'field' => 'CreatedByThings.test_list', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'decimal', 'label' => 'Testmetric Amount', 'field' => 'CreatedByThings.testmetric_amount', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Testmetric Unit', 'field' => 'CreatedByThings.testmetric_unit', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'decimal', 'label' => 'Testmoney Amount', 'field' => 'CreatedByThings.testmoney_amount', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Testmoney Currency', 'field' => 'CreatedByThings.testmoney_currency', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'list', 'label' => 'Title', 'field' => 'CreatedByThings.title', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'boolean', 'label' => 'Vip', 'field' => 'CreatedByThings.vip', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'url', 'label' => 'Website', 'field' => 'CreatedByThings.website', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'time', 'label' => 'Work Start', 'field' => 'CreatedByThings.work_start', 'association' => 'oneToMany', 'group' => 'Things (Created By)'],
                    ['type' => 'reminder', 'label' => 'Appointment', 'field' => 'ModifiedByThings.appointment', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'decimal', 'label' => 'Area Amount', 'field' => 'ModifiedByThings.area_amount', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Area Unit', 'field' => 'ModifiedByThings.area_unit', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'related', 'label' => 'Assigned To', 'display_field' => 'name', 'source' => 'users', 'field' => 'ModifiedByThings.assigned_to', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'blob', 'label' => 'Bio', 'field' => 'ModifiedByThings.bio', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'country', 'label' => 'Country', 'field' => 'ModifiedByThings.country', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'ModifiedByThings.created', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'related', 'label' => 'Created By', 'display_field' => 'name', 'source' => 'users', 'field' => 'ModifiedByThings.created_by', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'currency', 'label' => 'Currency', 'field' => 'ModifiedByThings.currency', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'date', 'label' => 'Date Of Birth', 'field' => 'ModifiedByThings.date_of_birth', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'text', 'label' => 'label description', 'field' => 'ModifiedByThings.description', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'email', 'label' => 'Email', 'field' => 'ModifiedByThings.email', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Gender', 'field' => 'ModifiedByThings.gender', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Language', 'field' => 'ModifiedByThings.language', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'integer', 'label' => 'Level', 'field' => 'ModifiedByThings.level', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'ModifiedByThings.modified', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'related', 'label' => 'Modified By', 'display_field' => 'name', 'source' => 'users', 'field' => 'ModifiedByThings.modified_by', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'string', 'label' => 'label name', 'field' => 'ModifiedByThings.name', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'phone', 'label' => 'Phone', 'field' => 'ModifiedByThings.phone', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'related', 'label' => 'Primary Thing', 'display_field' => 'name', 'source' => 'things', 'field' => 'ModifiedByThings.primary_thing', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'decimal', 'label' => 'Rate', 'field' => 'ModifiedByThings.rate', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'decimal', 'label' => 'Salary Amount', 'field' => 'ModifiedByThings.salary_amount', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Salary Currency', 'field' => 'ModifiedByThings.salary_currency', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'datetime', 'label' => 'Sample Date', 'field' => 'ModifiedByThings.sample_date', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'sublist', 'label' => 'Test List', 'field' => 'ModifiedByThings.test_list', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'decimal', 'label' => 'Testmetric Amount', 'field' => 'ModifiedByThings.testmetric_amount', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Testmetric Unit', 'field' => 'ModifiedByThings.testmetric_unit', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'decimal', 'label' => 'Testmoney Amount', 'field' => 'ModifiedByThings.testmoney_amount', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Testmoney Currency', 'field' => 'ModifiedByThings.testmoney_currency', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'list', 'label' => 'Title', 'field' => 'ModifiedByThings.title', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Vip', 'field' => 'ModifiedByThings.vip', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'url', 'label' => 'Website', 'field' => 'ModifiedByThings.website', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'time', 'label' => 'Work Start', 'field' => 'ModifiedByThings.work_start', 'association' => 'oneToMany', 'group' => 'Things (Modified By)'],
                    ['type' => 'boolean', 'label' => 'Active', 'field' => 'SocialAccounts.active', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Avatar', 'field' => 'SocialAccounts.avatar', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'SocialAccounts.created', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'text', 'label' => 'Data', 'field' => 'SocialAccounts.data', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'text', 'label' => 'Description', 'field' => 'SocialAccounts.description', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'SocialAccounts.modified', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Provider', 'field' => 'SocialAccounts.provider', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Reference', 'field' => 'SocialAccounts.reference', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Token', 'field' => 'SocialAccounts.token', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'datetime', 'label' => 'Token Expires', 'field' => 'SocialAccounts.token_expires', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Token Secret', 'field' => 'SocialAccounts.token_secret', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'string', 'label' => 'Username', 'field' => 'SocialAccounts.username', 'association' => 'oneToMany', 'group' => 'CakeDC/Users.SocialAccounts (User Id)'],
                    ['type' => 'datetime', 'label' => 'Activation Date', 'field' => 'Users.activation_date', 'group' => 'Users'],
                    ['type' => 'boolean', 'label' => 'Active', 'field' => 'Users.active', 'group' => 'Users'],
                    ['type' => 'text', 'label' => 'Additional Data', 'field' => 'Users.additional_data', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Api Token', 'field' => 'Users.api_token', 'group' => 'Users'],
                    ['type' => 'date', 'label' => 'Birthdate', 'field' => 'Users.birthdate', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Company', 'field' => 'Users.company', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Country', 'field' => 'Users.country', 'group' => 'Users'],
                    ['type' => 'datetime', 'label' => 'Created', 'field' => 'Users.created', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Department', 'field' => 'Users.department', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Email', 'field' => 'Users.email', 'group' => 'Users'],
                    ['type' => 'text', 'label' => 'Extras', 'field' => 'Users.extras', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Fax', 'field' => 'Users.fax', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'First Name', 'field' => 'Users.first_name', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Gender', 'field' => 'Users.gender', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Initials', 'field' => 'Users.initials', 'group' => 'Users'],
                    ['type' => 'boolean', 'label' => 'Is Superuser', 'field' => 'Users.is_superuser', 'group' => 'Users'],
                    ['type' => 'boolean', 'label' => 'Is Supervisor', 'field' => 'Users.is_supervisor', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Last Name', 'field' => 'Users.last_name', 'group' => 'Users'],
                    ['type' => 'datetime', 'label' => 'Modified', 'field' => 'Users.modified', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Password', 'field' => 'Users.password', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Phone Extension', 'field' => 'Users.phone_extension', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Phone Home', 'field' => 'Users.phone_home', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Phone Mobile', 'field' => 'Users.phone_mobile', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Phone Office', 'field' => 'Users.phone_office', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Position', 'field' => 'Users.position', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Role', 'field' => 'Users.role', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Secret', 'field' => 'Users.secret', 'group' => 'Users'],
                    ['type' => 'boolean', 'label' => 'Secret Verified', 'field' => 'Users.secret_verified', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Team', 'field' => 'Users.team', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Token', 'field' => 'Users.token', 'group' => 'Users'],
                    ['type' => 'datetime', 'label' => 'Token Expires', 'field' => 'Users.token_expires', 'group' => 'Users'],
                    ['type' => 'datetime', 'label' => 'Tos Date', 'field' => 'Users.tos_date', 'group' => 'Users'],
                    ['type' => 'string', 'label' => 'Username', 'field' => 'Users.username', 'group' => 'Users'],
                ],
            ],
        ];
    }
}
