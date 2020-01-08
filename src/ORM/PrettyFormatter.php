<?php

namespace App\ORM;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Webmozart\Assert\Assert;

final class PrettyFormatter
{
    /**
     * Prettifies ResultSet for UI purposes. It includes html tags.
     *
     * @param \Cake\Collection\CollectionInterface $results ResultSet
     * @return \Cake\Collection\CollectionInterface
     */
    public function __invoke(CollectionInterface $results): CollectionInterface
    {
        return $results->map(function (EntityInterface $entity) {
            static $table = null;
            if (null === $table) {
                $table = TableRegistry::getTableLocator()->get($entity->getSource());
            }

            return self::format($entity, $table);
        });
    }

    /**
     * Formats ResultSet using the provided callable method.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @return \Cake\Datasource\EntityInterface
     */
    private static function format(EntityInterface $entity, Table $table): EntityInterface
    {
        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            self::formatField($entity, $table, $field);
        }

        return $entity;
    }

    /**
     * Formats specified field.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @param string $field Field name
     * @return void
     */
    private static function formatField(EntityInterface $entity, Table $table, string $field): void
    {
        if ('_permissions' === $field) {
            return;
        }

        if ('_matchingData' === $field) {
            $entity->set('_matchingData', self::formatMatchingData($entity->get('_matchingData'), $table));

            return;
        }

        if ($entity->get($field) instanceof EntityInterface) {
            $entity->set($field, self::formatAssociatedEntity($entity->get($field), $table, $field));

            return;
        }

        /**
         * Handles the special cases of combined fields, this will go away
         * once we properly separate database column and UI field definitions.
         */
        if (self::isCombinedField($field)) {
            self::formatCombinedField($entity, $table, $field);

            return;
        }

        static $factory = null;
        if (null === $factory) {
            $factory = new FieldHandlerFactory();
        }
        // current model field
        if ($table->hasField($field)) {
            $entity->set($field, $factory->renderValue($table, $field, $entity->get($field)));
        }
    }

    /**
     * Formats related _matchingData.
     *
     * @param mixed[] $data Related data
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function formatMatchingData(array $data, Table $table): array
    {
        $result = [];
        foreach ($data as $associationName => $relatedEntity) {
            $result[$associationName] = self::format(
                $relatedEntity,
                $table->getAssociation($associationName)->getTarget()
            );
        }

        return $result;
    }

    /**
     * Formats associated entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Associated entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @param string $field Field name
     * @return \Cake\Datasource\EntityInterface
     */
    private static function formatAssociatedEntity(EntityInterface $entity, Table $table, string $field): EntityInterface
    {
        $association = $table->associations()->getByProperty($field);
        Assert::isInstanceOf($association, \Cake\ORM\Association::class);

        return self::format($entity, $association->getTarget());
    }

    /**
     * Combined field detector.
     *
     * @param string $field Field name
     * @return bool
     */
    private static function isCombinedField(string $field): bool
    {
        foreach (['_amount', '_currency', '_unit'] as $item) {
            $strlen = strlen($item);
            if ($item === substr($field, -$strlen, $strlen)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formats combined field.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @param string $field Field name
     * @return void
     */
    private static function formatCombinedField(EntityInterface $entity, Table $table, string $field): void
    {
        static $factory = null;
        if (null === $factory) {
            $factory = new FieldHandlerFactory();
        }

        $mapping = [
            '_amount' => 'decimal',
            '_currency' => 'currency(currencies)',
            '_unit' => 'list(units_area)'];

        $parts = explode('_', $field);
        $index = '_' . end($parts);

        $entity->set($field, $factory->renderValue(
            $table,
            $field,
            $entity->get($field),
            ['entity' => $entity, 'fieldDefinitions' => ['type' => $mapping[$index]]]
        ));
    }
}
