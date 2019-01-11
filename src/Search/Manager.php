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
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Utility\Hash;
use Cake\View\View;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

final class Manager
{
    private const FILTER_MAP = [
        'is' => \Search\Filter\Equal::class,
        'is_not' => \Search\Filter\NotEqual::class,
    ];

    private $table;
    private $user;

    public function __construct(RepositoryInterface $table, array $user)
    {
        $this->table = $table;
        $this->user = $user;
    }

    public function getFields() : array
    {

    }

    public function getOptionsFromRequest(array $data, array $params)
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

        if (Hash::get($data, 'display_columns')) {
            $result['fields'] = array_merge((array)$this->table->getPrimaryKey(), Hash::get($data, 'display_columns'));
        }

        if (Hash::get($params, 'sort')) {
            $result['order'] = [Hash::get($params, 'sort') => Hash::get($params, 'direction', 'desc')];
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

    /**
     * Method that formats search result-set.
     *
     * @param \Cake\Datasource\ResultSetInterface $resultSet Result-set instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param mixed[] $user Current user info
     * @param bool $group Group flag
     * @return mixed[]
     */
    public static function resultSetFormatter(ResultSetInterface $resultSet, RepositoryInterface $table, array $user, $group = false) : array
    {
        $result = [];
        $view = new View();
        $factory = new FieldHandlerFactory();
        foreach ($resultSet as $entity) {
            $row = self::formatEntity($entity, $table, $factory);

            if (! $group) {
                $row['actions_column'] = $view->element('/Search/search-view-actions', [
                    'entity' => $entity,
                    'model' => $table->getRegistryAlias(),
                    'user' => $user
                ]);
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Method that formats search result-set entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\Datasource\RepositoryInterface|string $table Table instance
     * @param \CsvMigrations\FieldHandlers\FieldHandlerFactory $factory FieldHandlerFactory instance
     * @return mixed[]
     */
    private static function formatEntity(EntityInterface $entity, RepositoryInterface $table, FieldHandlerFactory $factory) : array
    {
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
                    $factory
                ));
            }
        }

        return $result;
    }
}
