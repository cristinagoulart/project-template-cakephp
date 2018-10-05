<?php
namespace App\Event\Model;

use ArrayObject;
use Cake\Datasource\QueryInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Association;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * This class is responsible for adding Module's lookup fields into the query's
 * "where" clause and is applied system wide. The logic is triggered only if the
 * "lookup = true" flag is used in the Query's options.
 *
 */
class LookupListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => 'beforeFind',
            'Model.beforeMarshal' => 'beforeMarshal'
        ];
    }

    /**
     * Apply lookup fields to Query's where clause.
     *
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \ArrayObject $options Query options
     * @param bool $primary Primary Standalone Query flag
     * @return void
     */
    public function beforeFind(Event $event, QueryInterface $query, ArrayObject $options, $primary)
    {
        if (! $primary) {
            return;
        }

        if (! isset($options['lookup']) || ! $options['lookup']) {
            return;
        }

        if (! isset($options['value'])) {
            return;
        }

        $config = (new ModuleConfig(ConfigType::MODULE(), $event->getSubject()->getAlias()))->parse();
        if (empty($config->table->lookup_fields)) {
            // fail-safe binding of primary key to query's where clause, if lookup
            // fields are not defined, to avoid random record retrieval.
            $query->where([
                $event->getSubject()->aliasField($event->getSubject()->getPrimaryKey()) => $options['value']
            ]);

            return;
        }

        foreach ($config->table->lookup_fields as $field) {
            $query->orWhere([
                $event->getSubject()->aliasField($field) => $options['value']
            ]);
        }
    }

    /**
     * Checks Entity's association fields (foreign keys) values and query's the database to find
     * the associated record. If the record is not found, it query's the database again to find it by its
     * display field. If found it replaces the associated field's value with the records id.
     *
     * This is useful for cases where the display field value is used on the associated field. For example
     * a new post is created and in the 'owner' field the username of the user is used instead of its uuid.
     *
     * BEFORE:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => 'admin',
     * }
     *
     * AFTER:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => '77dd9203-3f21-4571-8843-0264ae1cfa48',
     * }
     *
     * @param \Cake\Event\Event $event Event object
     * @param \ArrayObject $data Request data
     * @param \ArrayObject $options Query options
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (! isset($options['lookup']) || ! (bool)$options['lookup']) {
            return;
        }

        foreach ($event->getSubject()->associations() as $association) {
            if (! $this->isValidAssociation($association)) {
                continue;
            }

            $this->setRelatedByLookupField($association, $data);
        }
    }

    /**
     * Sets related record value by lookup fields.
     *
     * @param \Cake\ORM\Association $association Table association
     * @param \ArrayObject $data Request data
     * @return void
     */
    private function setRelatedByLookupField(Association $association, ArrayObject $data)
    {
        // skip if foreign key is not set in the request data
        if (empty($data[$association->getForeignKey()])) {
            return;
        }

        $lookupFields = $this->getLookupFields($association->className());
        if (empty($lookupFields)) {
            return;
        }

        if ($this->hasPrimaryKey($association, $data)) {
            return;
        }

        $relatedEntity = $this->getRelatedEntity($association, $data, $lookupFields);
        if (is_null($relatedEntity)) {
            return;
        }

        $data[$association->getForeignKey()] = $relatedEntity->get($association->getPrimaryKey());
    }

    /**
     * Validates if association can be used for lookup functionality.
     *
     * @param \Cake\ORM\Association $association Table association
     * @return bool
     */
    private function isValidAssociation(Association $association)
    {
        if (Association::MANY_TO_ONE !== $association->type()) {
            return false;
        }

        if (is_null($association->className())) {
            return false;
        }

        return true;
    }

    /**
     * Module lookup fields getter.
     *
     * @param string $moduleName Module name
     * @return array
     */
    private function getLookupFields($moduleName)
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $moduleName))->parse();

        return $config->table->lookup_fields;
    }

    /**
     * Checks if related record is found by primary key
     *
     * @param \Cake\ORM\Association $association Table association
     * @param \ArrayObject $data Request data
     * @return bool
     */
    private function hasPrimaryKey(Association $association, ArrayObject $data)
    {
        $query = $association->getTarget()->find('all')
            ->where([$association->primaryKey() => $data[$association->getForeignKey()]])
            ->limit(1);

        return ! $query->isEmpty();
    }

    /**
     * Retrieves associated entity.
     *
     * @param \Cake\ORM\Association $association Table association
     * @param \ArrayObject $data Request data
     * @param array $fields Lookup fields
     * @return \Cake\Datasource\EntityInterface|null
     */
    private function getRelatedEntity(Association $association, ArrayObject $data, array $fields)
    {
        $query = $association->getTarget()
            ->find('all')
            ->select($association->getPrimaryKey())
            ->limit(1);

        foreach ($fields as $field) {
            $query->orWhere([$field => $data[$association->getForeignKey()]]);
        }

        return $query->first();
    }
}
