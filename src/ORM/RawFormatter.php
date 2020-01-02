<?php

namespace App\ORM;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Webmozart\Assert\Assert;

final class RawFormatter
{
    /**
     * Raw ResultSet formatter.
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
     * Formats ResultSet.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @return \Cake\Datasource\EntityInterface
     */
    private static function format(EntityInterface $entity, Table $table): EntityInterface
    {
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
                Assert::isInstanceOf($association, Association::class);
                $entity->set($field, self::format($entity->get($field), $association->getTarget()));

                continue;
            }

            // current model field
            $entity->set($field, self::formatValue($table, $field, $entity->get($field)));
        }

        return $entity;
    }

    /**
     * Formats value.
     *
     * @param \Cake\ORM\Table $table ORM table
     * @param string $field Field name
     * @param mixed $value Field value
     * @return mixed
     */
    private static function formatValue(Table $table, string $field, $value)
    {
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if ($value instanceof \Cake\I18n\Date) {
            return $value->format('Y-m-d');
        }

        if ($value instanceof \Cake\I18n\Time) {
            $format = \DateTime::ATOM;
            if ('time' === $table->getSchema()->getColumnType($field)) {
                $format = 'H:i';
            }

            return $value->format($format);
        }

        return $value;
    }
}
