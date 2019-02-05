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

    public static function getOptionsFromRequest(array $data, array $params)
    {
        $result = [];

        foreach (Hash::get($data, 'criteria', []) as $field => $fieldCriteria) {
            foreach ($fieldCriteria as $criteria) {
                if (! array_key_exists($criteria['operator'], self::FILTER_MAP)) {
                    throw new \RuntimeException(sprintf('Unsupported filter provided: %s', $criteria['operator']));
                }

                $result['data'][] = [
                    'field' => $field,
                    'operator' => self::FILTER_MAP[$criteria['operator']],
                    'value' => $criteria['value']
                ];
            }
        }

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
     * @param mixed $value Field value
     * @return mixed
     */
    public static function applyMagicValue($value)
    {
        switch (gettype($value)) {
            case 'string':
                $value = (new MagicValue($value, $this->user))->get();
                break;

            case 'array':
                foreach ($value as $key => $val) {
                    $value[$key] = (new MagicValue($val, $this->user))->get();
                }
                break;
        }

        return $value;
    }
}
