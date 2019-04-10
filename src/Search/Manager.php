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
namespace App\Search;

use Cake\Utility\Hash;
use Qobo\Utils\Utility\User;
use Search\Filter\Contains;
use Search\Filter\EndsWith;
use Search\Filter\Equal;
use Search\Filter\Greater;
use Search\Filter\Less;
use Search\Filter\NotContains;
use Search\Filter\NotEqual;
use Search\Filter\StartsWith;

final class Manager
{
    private const FILTER_MAP = [
        'is' => Equal::class,
        'is_not' => NotEqual::class,
        'greater' => Greater::class,
        'less' => Less::class,
        'contains' => Contains::class,
        'not_contains' => NotContains::class,
        'starts_with' => StartsWith::class,
        'ends_with' => EndsWith::class
    ];

    /**
     * Retrieve search options from HTTP request.
     *
     * @param mixed[] $data Request data
     * @param mixed[] $params Request params
     * @return mixed[]
     */
    public static function getOptionsFromRequest(array $data, array $params) : array
    {
        $result = [];

        foreach (Hash::get($data, 'criteria', []) as $field => $fieldCriteria) {
            foreach ($fieldCriteria as $criteria) {
                if (! array_key_exists($criteria['operator'], self::FILTER_MAP)) {
                    throw new \RuntimeException(sprintf('Unsupported filter provided: %s', $criteria['operator']));
                }

                switch (gettype($criteria['value'])) {
                    case 'string':
                        $value = self::applyMagicValue($criteria['value']);
                        break;

                    case 'array':
                        $value = self::applyMagicValues($criteria['value']);
                        break;

                    default:
                        $value = $criteria['value'];
                        break;
                }

                $result['data'][] = [
                    'field' => $field,
                    'operator' => self::FILTER_MAP[$criteria['operator']],
                    'value' => $value
                ];
            }
        }

        $result['aggregator'] = Hash::get($data, 'aggregator', 'AND');

        if (Hash::get($data, 'fields')) {
            $result['fields'] = Hash::get($data, 'fields');
        }

        // if (Hash::get($params, 'sort')) {
        //    $result['order'] = [Hash::get($params, 'sort') => Hash::get($params, 'direction', 'asc')];
        // }
        if (Hash::get($data, 'sort')) {
            $result['order'] = [Hash::get($data, 'sort') => Hash::get($data, 'direction', 'asc')];
        }

        if (Hash::get($data, 'group_by')) {
            $result['group'] = Hash::get($data, 'group_by');
        }

        return $result;
    }

    /**
     * Magic value handler.
     *
     * @param string $value Field value
     * @return string
     */
    private static function applyMagicValue(string $value) : string
    {
        return (new MagicValue($value, User::getCurrentUser()))->get();
    }

    /**
     * Magic values handler.
     *
     * @param string[] $values Field values
     * @return string[]
     */
    private static function applyMagicValues(array $values) : array
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = (new MagicValue($value, User::getCurrentUser()))->get();
        }

        return $result;
    }
}
