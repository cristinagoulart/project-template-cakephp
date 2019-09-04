<?php
namespace App\Test\TestCase\Utility;

use App\Utility\Search;
use Cake\TestSuite\TestCase;
use Qobo\Utils\Utility\User;
use Search\Model\Entity\SavedSearch;

class SearchTest extends TestCase
{
    public $fixtures = [
        'plugin.Search.saved_searches'
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
            'Things.id',
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
            'content' => [
                'saved' => [
                    'group_by' => 'Things.created'
                ]
            ]
        ]);

        $result = Search::getChartOptions($savedSearch);

        $this->assertCount(3, $result);
        $this->assertSame([], array_diff(['pie', 'bar', 'funnelChart'], array_column($result, 'chart')));
    }

    public function testGetChartOptionsWithoutGroupBy() : void
    {
        $savedSearch = new SavedSearch([
            'name' => 'Things NOT grouped by',
            'model' => 'Things',
            'content' => [
                'saved' => [
                    'group_by' => ''
                ]
            ]
        ]);

        $this->assertSame([], Search::getChartOptions($savedSearch));
    }
}
