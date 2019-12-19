<?php

namespace App\ORM;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Webmozart\Assert\Assert;

final class FlatFormatter
{
    /**
     * Flattens ResultSet. Useful for returning associated data in a flat format.
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

            $newEntity = $table->newEntity();
            $data = self::flatten($entity, $table);
            array_walk($data, function ($value, $field) use ($newEntity) {
                $newEntity->set($field, $value);
            });

            return $newEntity;
        });
    }

    /**
     * Flattens ResultSet.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function flatten(EntityInterface $entity, Table $table): array
    {
        $result = [];
        foreach (array_diff($entity->visibleProperties(), $entity->getVirtual()) as $field) {
            if ('_permissions' === $field) {
                $result[$field] = $entity->get($field);
                continue;
            }

            if ('_matchingData' === $field) {
                foreach ($entity->get('_matchingData') as $associationName => $relatedEntity) {
                    $result = array_merge(
                        $result,
                        self::flatten($relatedEntity, $table->getAssociation($associationName)->getTarget())
                    );
                }
                continue;
            }

            if ($entity->get($field) instanceof EntityInterface) {
                $association = $table->associations()->getByProperty($field);
                Assert::isInstanceOf($association, \Cake\ORM\Association::class);
                $result = array_merge($result, self::flatten($entity->get($field), $association->getTarget()));

                continue;
            }

            // current table field
            $result[$table->aliasField($field)] = $entity->get($field);
        }

        return $result;
    }
}
