<?php

/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Test\TestCase\Search;

use App\Search\Manager;
use Cake\TestSuite\TestCase;
use Qobo\Utils\Utility\User;
use Webmozart\Assert\Assert;

class ManagerTest extends TestCase
{
    public $fixtures = [
        'app.SavedSearches',
        'app.Things',
        'app.Users',
        'plugin.Groups.groups',
        'plugin.Groups.groups_users',
        'plugin.RolesCapabilities.groups_roles',
        'plugin.RolesCapabilities.roles',
    ];

    public function setUp(): void
    {
        parent::setUp();

        User::setCurrentUser(['id' => '00000000-0000-0000-0000-000000000001']);
    }

    public function testGetOptionsFromRequest(): void
    {
        $expected = [
            'data' => [
                ['field' => 'country', 'operator' => 'is', 'value' => ['CY']],
                ['field' => 'avg(budget)', 'operator' => 'greater', 'value' => 1000]
            ],
            'fields' => ['created', 'modified', 'avg(budget)', 'count(status)'],
            'conjunction' => 'OR',
            'order' => ['created' => 'asc'],
            'group' => 'status'
        ];

        $data = [
            'direction' => 'asc',
            'sort' => 'created',
            'fields' => ['created', 'modified', 'avg(budget)', 'count(status)'],
            'criteria' => [
                'country' => [
                    ['operator' => 'is', 'value' => ['CY']]
                ],
                'avg(budget)' => [
                    ['operator' => 'greater', 'value' => 1000]
                ]
            ],
            'conjunction' => 'OR',
            'group_by' => 'status',
            'aggregator' => 'AND'
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithMagicValue(): void
    {
        $data = [
            'criteria' => [
                'assigned_to' => [
                    ['operator' => 'is_not', 'value' => '%%me%%']
                ]
            ]
        ];

        $expected = [
            'data' => [
                ['field' => 'assigned_to', 'operator' => 'is_not', 'value' => User::getCurrentUser()['id']]
            ]
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithMagicValues(): void
    {
        $data = [
            'criteria' => [
                'assigned_to' => [
                    ['operator' => 'is_not', 'value' => ['%%me%%', '%%me%%']]
                ]
            ]
        ];

        $expected = [
            'data' => [
                ['field' => 'assigned_to', 'operator' => 'is_not', 'value' => [User::getCurrentUser()['id'], User::getCurrentUser()['id']]]
            ]
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithGoupBy(): void
    {
        $expected = ['group' => 'foo'];

        $this->assertSame($expected, Manager::getOptionsFromRequest(['group_by' => 'foo'], []));
    }

    public function testIncludePrimaryKey(): void
    {
        $this->assertTrue(Manager::includePrimaryKey([]));
    }

    public function testIncludePrimaryKeyWithGoupBy(): void
    {
        $this->assertFalse(Manager::includePrimaryKey(['group' => 'foo']));
    }

    public function testIncludePrimaryKeyWithAggregate(): void
    {
        $this->assertFalse(Manager::includePrimaryKey(['fields' => ['count(status)']]));
    }

    public function testGetSystemSearch(): void
    {
        $this->assertNull(Manager::getSystemSearch('Things'));
    }

    public function testGetSystemSearchAfterCreation(): void
    {
        Manager::createSystemSearch('Things');

        $savedSearch = Manager::getSystemSearch('Things');

        Assert::isInstanceOf($savedSearch, \Search\Model\Entity\SavedSearch::class);
        $this->assertSame('Things', $savedSearch->get('model'));
        $this->assertTrue($savedSearch->get('system'));
    }

    public function testCreateSystemSearch(): void
    {
        $savedSearch = Manager::createSystemSearch('Things');

        $this->assertSame('Default Things search', $savedSearch->get('name'));
        $this->assertSame('Things', $savedSearch->get('model'));
        $this->assertSame('00000000-0000-0000-0000-000000000001', $savedSearch->get('user_id'));
        $this->assertSame([], $savedSearch->get('criteria'));
        $this->assertSame('AND', $savedSearch->get('conjunction'));
        $this->assertSame('DESC', $savedSearch->get('order_by_direction'));
        $this->assertTrue($savedSearch->get('system'));
        $this->assertTrue($savedSearch->get('is_editable'));
    }
}
