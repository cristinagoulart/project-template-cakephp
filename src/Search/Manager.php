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

use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Webmozart\Assert\Assert;

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

    /**
     * Method that formats search result-set.
     *
     * @param \Cake\Datasource\ResultSetInterface $entities Search result-set
     * @param \Cake\ORM\Table $table Table instance
     * @param bool $withPermissions Whether to include access permissions
     * @return mixed[]
     */
    public static function formatEntities(ResultSetInterface $entities, Table $table, bool $withPermissions = false) : array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = self::formatEntity($entity, $table, $withPermissions);
        }

        return $result;
    }

    /**
     * Method that formats search result-set entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @param bool $withPermissions Whether to include access permissions
     * @return mixed[]
     */
    private static function formatEntity(EntityInterface $entity, Table $table, bool $withPermissions = false) : array
    {
        static $factory = null;
        if (null === $factory) {
            $factory = new FieldHandlerFactory();
        }

        $result = [];
        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            // current table field
            if ('_matchingData' !== $field) {
                $result[$table->aliasField($field)] = $factory->renderValue($table, $field, $entity->get($field));
                continue;
            }

            foreach ($entity->get('_matchingData') as $associationName => $relatedEntity) {
                $result = array_merge($result, self::formatEntity(
                    $relatedEntity,
                    $table->getAssociation($associationName)->getTarget(),
                    false
                ));
            }
        }

        if ($withPermissions) {
            $primaryKey = $table->getPrimaryKey();
            Assert::string($primaryKey);
            $result['_permissions'] = self::getPermissions($entity->get($primaryKey), $table);
        }

        return $result;
    }

    /**
     * Returns entity access permissions.
     *
     * @param string $id Entity ID
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function getPermissions(string $id, Table $table) : array
    {
        static $factory = null;
        if (null === $factory) {
            $factory = new AccessFactory();
        }

        list($plugin, $controller) = pluginSplit($table->getAlias());

        $urls = [
            'view' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id],
            'edit' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id],
            'delete' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $id]
        ];

        return [
            'view' => $factory->hasAccess($urls['view'], User::getCurrentUser()),
            'edit' => $factory->hasAccess($urls['edit'], User::getCurrentUser()),
            'delete' => $factory->hasAccess($urls['delete'], User::getCurrentUser())
        ];
    }
}
