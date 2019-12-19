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

use App\Utility\Search;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Search\Aggregate\AggregateInterface;
use Search\Model\Entity\SavedSearch;
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
    public static function getOptionsFromRequest(array $data, array $params): array
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
     * Checks whether the primary key must be included in the search fields.
     *
     * @param mixed[] $options Search options
     * @return bool
     */
    public static function includePrimaryKey(array $options): bool
    {
        if (array_key_exists('group', $options)) {
            return false;
        }

        foreach ((array)Hash::get($options, 'fields', []) as $item) {
            if (1 === preg_match(AggregateInterface::AGGREGATE_PATTERN, $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Criteria getter.
     *
     * @param mixed[] $criteria Search criteria
     * @return mixed[]
     */
    private static function getCriteria(array $criteria): array
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
    private static function getFieldCriteria(string $field, array $criteria): array
    {
        $result = [];
        foreach ($criteria as $item) {
            $result[] = [
                'field' => $field,
                'operator' => $item['operator'],
                'value' => self::applyMagicValue($item['value']),
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
    public static function formatEntities(ResultSetInterface $entities, Table $table, bool $withPermissions = false): array
    {
        deprecationWarning(
            'Manager::formatEntities() method is deprecated. ' .
            'Use \App\ORM\PrettyFormatter class instead.'
        );

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
    private static function formatEntity(EntityInterface $entity, Table $table, bool $withPermissions = false): array
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
    private static function getPermissions(string $id, Table $table): array
    {
        static $factory = null;
        if (null === $factory) {
            $factory = new AccessFactory();
        }

        list($plugin, $controller) = pluginSplit($table->getAlias());

        $urls = [
            'view' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id],
            'edit' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id],
            'delete' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $id],
        ];

        array_walk($urls, function (&$item, $key) use ($factory) {
            $className = sprintf('\App\Controller\%sController', $item['controller']);
            $item = $factory->hasAccess($item, User::getCurrentUser()) && method_exists($className, $item['action']);
        });

        return $urls;
    }

    /**
     * System search getter.
     *
     * @param string $model Model name
     * @return \Search\Model\Entity\SavedSearch|null
     */
    public static function getSystemSearch(string $model): ?SavedSearch
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $savedSearch = $table->find()
            ->enableHydration(true)
            ->where(['SavedSearches.model' => $model, 'SavedSearches.system' => true])
            ->first();

        Assert::nullOrIsInstanceOf($savedSearch, SavedSearch::class);

        return $savedSearch;
    }

    /**
     * Creates system search for provided model.
     *
     * @param string $model Model name
     * @return \Search\Model\Entity\SavedSearch
     */
    public static function createSystemSearch(string $model): SavedSearch
    {
        $user = TableRegistry::getTableLocator()->get('Users')
            ->find()
            ->where(['is_superuser' => true])
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($user, \App\Model\Entity\User::class);

        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        $displayFields = Search::getDisplayFields($model);
        $savedSearch = $table->newEntity([
            'name' => sprintf('Default %s search', Inflector::humanize(Inflector::underscore($model))),
            'model' => $model,
            'system' => true,
            'user_id' => $user->get('id'),
            'criteria' => [],
            'conjunction' => \Search\Criteria\Conjunction::DEFAULT_CONJUNCTION,
            'fields' => $displayFields,
            'order_by_direction' => \Search\Criteria\Direction::DEFAULT_DIRECTION,
            'order_by_field' => current($displayFields),
        ]);

        $table->saveOrFail($savedSearch);

        Assert::isInstanceOf($savedSearch, SavedSearch::class);

        return $savedSearch;
    }
}
