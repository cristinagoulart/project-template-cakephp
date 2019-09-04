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
    public function setUp() : void
    {
        parent::setUp();

        User::setCurrentUser(['id' => '123']);
    }

    public function testGetOptionsFromRequest() : void
    {
        $data = [
            'direction' => 'asc',
            'sort' => 'created',
            'fields' => ['created', 'modified'],
            'criteria' => [
                'country' => [
                    '123' => ['operator' => 'is', 'value' => ['CY']]
                ]
            ],
            'group_by' => '',
            'aggregator' => 'AND'
        ];

        $expected = [
            'data' => [
                ['field' => 'country', 'operator' => 'Search\Filter\Equal', 'value' => ['CY']]
            ],
            'conjunction' => 'AND',
            'fields' => ['created', 'modified'],
            'order' => ['created' => 'asc']
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithMagicValue() : void
    {
        $data = [
            'criteria' => [
                'assigned_to' => [
                    '123' => ['operator' => 'is_not', 'value' => '%%me%%']
                ]
            ]
        ];

        $expected = [
            'data' => [
                ['field' => 'assigned_to', 'operator' => 'Search\Filter\NotEqual', 'value' => '123']
            ],
            'conjunction' => 'AND'
        ];

        $this->assertSame($expected, Manager::getOptionsFromRequest($data, []));
    }

    public function testGetOptionsFromRequestWithGoupBy() : void
    {
        $expected = ['conjunction' => 'AND', 'group' => 'foo'];

        $this->assertSame($expected, Manager::getOptionsFromRequest(['group_by' => 'foo'], []));
    }

    public function testGetOptionsFromRequestWithInvalidOperator() : void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'criteria' => [
                'assigned_to' => [
                    '123' => ['operator' => 'INVALID OPERATOR']
                ]
            ]
        ];

        Manager::getOptionsFromRequest($data, []);
    }
}
