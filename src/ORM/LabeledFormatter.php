<?php

namespace App\ORM;

use App\Utility\FieldList;
use App\Utility\Model;
use Cake\Collection\CollectionInterface;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Webmozart\Assert\Assert;

final class LabeledFormatter
{
    /**
     * Labels ResultSet for exporting purposes.
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
        $model = App::shortName(get_class($table), 'Model/Table', 'Table');

        $associations = Model::associations($model);
        $key = array_search($field, array_column($associations, 'foreign_key'));
        if (false !== $key) {
            return self::displayValueFromAssociation($table->getAssociation($associations[$key]['name']), $field, $value);
        }

        $list = new FieldList($model, $field);
        if ($list->has()) {
            $options = $list->options(['prettify' => false]);
            $key = array_search($value, array_column($options, 'value'));

            return false !== $key ? $options[$key]['label'] : $value;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if ($value instanceof \Cake\I18n\Date) {
            return $value->format('Y-m-d');
        }

        if ($value instanceof \Cake\I18n\Time) {
            $format = 'Y-m-d H:i:s';
            if ('time' === $table->getSchema()->getColumnType($field)) {
                $format = 'H:i';
            }

            return $value->format($format);
        }

        return $value;
    }

    /**
     * Retrieves corresponding display value from related record.
     *
     * This method will recurse until it retrieves a non-primary-key value.
     *
     * @param \Cake\ORM\Association $association Association
     * @param string $field Field name
     * @param mixed $value Field value
     * @return mixed
     */
    private static function displayValueFromAssociation(Association $association, string $field, $value)
    {
        $targetTable = $association->getTarget();
        $displayField = $targetTable->getDisplayField();
        $primaryKey = $targetTable->getPrimaryKey();
        Assert::string($primaryKey);

        $entity = $targetTable->find()->select($displayField)->where([$primaryKey => $value])->first();
        if (null === $entity) {
            return $value;
        }
        Assert::isInstanceOf($entity, EntityInterface::class);
        $value = $entity->get($displayField);

        $model = App::shortName(get_class($targetTable), 'Model/Table', 'Table');
        $associations = Model::associations($model);
        $key = array_search($displayField, array_column($associations, 'foreign_key'));
        if (false !== $key) {
            return self::displayValueFromAssociation(
                $targetTable->getAssociation($associations[$key]['name']),
                $displayField,
                $value
            );
        }

        return $value;
    }
}
