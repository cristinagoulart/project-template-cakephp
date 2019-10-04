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

final class Manager
{
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

        if (Hash::get($data, 'criteria')) {
            $result['data'] = self::getCriteria(Hash::get($data, 'criteria', []));
        }

        if (Hash::get($data, 'fields')) {
            $result['fields'] = Hash::get($data, 'fields');
        }

        if (Hash::get($data, 'conjunction')) {
            $result['conjunction'] = Hash::get($data, 'conjunction', \Search\Criteria\Conjunction::DEFAULT_CONJUNCTION);
        }

        if (Hash::get($data, 'sort')) {
            $result['order'] = [Hash::get($data, 'sort') => Hash::get($data, 'direction', \Search\Criteria\Direction::DEFAULT_DIRECTION)];
        }

        if (Hash::get($data, 'group_by')) {
            $result['group'] = Hash::get($data, 'group_by');
        }

        return $result;
    }

    /**
     * Criteria getter.
     *
     * @param mixed[] $criteria Search criteria
     * @return mixed[]
     */
    private static function getCriteria(array $criteria) : array
    {
        $result = [];
        foreach ($criteria as $field => $items) {
            $result = array_merge($result, self::getFieldCriteria($field, $items));
        }

        return $result;
    }

    /**
     * Field criteria getter.
     *
     * @param string $field Field name
     * @param mixed[] $criteria Field search criteria
     * @return mixed[]
     */
    private static function getFieldCriteria(string $field, array $criteria) : array
    {
        $result = [];
        foreach ($criteria as $item) {
            $result[] = [
                'field' => $field,
                'operator' => $item['operator'],
                'value' => self::applyMagicValue($item['value'])
            ];
        }

        return $result;
    }

    /**
     * Magic value handler.
     *
     * @param mixed $value Field value
     * @return mixed
     */
    private static function applyMagicValue($value)
    {
        if (is_string($value)) {
            return (new MagicValue($value, User::getCurrentUser()))->get();
        }

        if (is_array($value)) {
            return array_map(function ($item) {
                return self::applyMagicValue($item);
            }, $value);
        }

        return $value;
    }
}
