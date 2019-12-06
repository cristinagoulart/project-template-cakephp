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
        static $factory = null;
        if (null === $factory) {
            $factory = new FieldHandlerFactory();
        }

        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            if ('_permissions' === $field) {
                continue;
            }

            if ('_matchingData' === $field) {
                $matchingData = [];
                foreach ($entity->get('_matchingData') as $associationName => $relatedEntity) {
                    $matchingData[$associationName] = self::format(
                        $relatedEntity,
                        $table->getAssociation($associationName)->getTarget()
                    );
                }
                $entity->set('_matchingData', $matchingData);

                continue;
            }

            if ($entity->get($field) instanceof EntityInterface) {
                $association = $table->associations()->getByProperty($field);
                Assert::isInstanceOf($association, \Cake\ORM\Association::class);
                $entity->set($field, self::format($entity->get($field), $association->getTarget()));

                continue;
            }

            $isCombinedField = false;
            $combinedFields = ['_amount' => 'decimal', '_currency' => 'currency(currencies)', '_unit' => 'list(units_area)'];
            /**
             * Handles the special cases of combined fields, this will go away
             * once we properly separate database column and UI field definitions.
             */
            foreach ($combinedFields as $fieldSuffix => $fieldType) {
                $strlen = strlen($fieldSuffix);
                if ($fieldSuffix === substr($field, -$strlen, $strlen)) {
                    $isCombinedField = true;
                    $entity->set($field, $factory->renderValue(
                        $table,
                        $field,
                        $entity->get($field),
                        ['entity' => $entity, 'fieldDefinitions' => ['type' => $fieldType]]
                    ));
                }
            }

            if ($isCombinedField) {
                continue;
            }

            // current model field
            if ($table->hasField($field)) {
                $entity->set($field, $factory->renderValue($table, $field, $entity->get($field)));
            }
        }

        return $entity;
    }
}
