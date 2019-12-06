<?php

namespace App\Utility;

use Cake\Core\App;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Webmozart\Assert\Assert;

final class Model
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * Constructor method.
     *
     * @param string $model Model name
     */
    private function __construct(string $model)
    {
        Assert::stringNotEmpty($model);

        $this->model = $model;
        $this->table = TableRegistry::getTableLocator()->get($model);
    }

    /**
     * Model fields getter.
     *
     * @param string $model Model name
     * @return mixed[]
     */
    public static function fields(string $model): array
    {
        $instance = new self($model);

        $result = [];
        foreach ($instance->table->getSchema()->columns() as $column) {
            $result = array_merge($result, [(new Field($instance->model, $column))->state()]);
        }

        return array_values(array_filter($result));
    }

    /**
     * Model associations getter.
     *
     * @param string $model Model name
     * @return mixed[]
     */
    public static function associations(string $model): array
    {
        return (new self($model))->getAssociations();
    }

    /**
     * Associations getter.
     *
     * @return mixed[]
     */
    private function getAssociations(): array
    {
        $result = [];

        foreach ($this->table->associations() as $association) {
            $result[] = [
                'name' => $association->getName(),
                'label' => self::getAssociationLabel($association),
                'model' => App::shortName(get_class($association->getTarget()), 'Model/Table', 'Table'),
                'type' => $association->type(),
                'primary_key' => $association->getBindingKey(),
                'foreign_key' => $association->getForeignKey()
            ];
        }

        return $result;
    }

    /**
     * Association label getter.
     *
     * @param \Cake\ORM\Association $association Association instance
     * @return string
     */
    private static function getAssociationLabel(Association $association): string
    {
        return sprintf(
            '%s (%s)',
            App::shortName(get_class($association->getTarget()), 'Model/Table', 'Table'),
            Inflector::humanize(implode(', ', (array)$association->getForeignKey()))
        );
    }
}
