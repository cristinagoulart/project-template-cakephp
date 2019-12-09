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

class ManagerTest extends TestCase
{
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
                ['field' => 'avg(budget)', 'operator' => 'greater', 'value' => 1000],
            ],
            'fields' => ['created', 'modified', 'avg(budget)', 'count(status)'],
            'order' => ['created' => 'asc'],
            'group' => 'status',
        ];

        $data = [
            'direction' => 'asc',
            'sort' => 'created',
            'fields' => ['created', 'modified', 'avg(budget)', 'count(status)'],
            'criteria' => [
                'country' => [
                    ['operator' => 'is', 'value' => ['CY']],
                ],
                'avg(budget)' => [
                    ['operator' => 'greater', 'value' => 1000],
                ],
            ],
            'group_by' => 'status',
            'aggregator' => 'AND',
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithMagicValue(): void
    {
        $data = [
            'criteria' => [
                'assigned_to' => [
                    ['operator' => 'is_not', 'value' => '%%me%%'],
                ],
            ],
        ];

        $expected = [
            'data' => [
                ['field' => 'assigned_to', 'operator' => 'is_not', 'value' => User::getCurrentUser()['id']],
            ],
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithMagicValues(): void
    {
        $data = [
            'criteria' => [
                'assigned_to' => [
                    ['operator' => 'is_not', 'value' => ['%%me%%', '%%me%%']],
                ],
            ],
        ];

        $expected = [
            'data' => [
                ['field' => 'assigned_to', 'operator' => 'is_not', 'value' => [User::getCurrentUser()['id'], User::getCurrentUser()['id']]],
            ],
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithGoupBy(): void
    {
        $expected = ['group' => 'foo'];

        $this->assertSame($expected, Manager::getOptionsFromRequest(['group_by' => 'foo'], []));
    }
}
