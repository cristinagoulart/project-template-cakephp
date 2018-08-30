<?php
namespace App\Event\Controller\Api;

use App\Event\EventName;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;
use CsvMigrations\FieldHandlers\Setting;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RuntimeException;

class LookupActionListener extends BaseActionListener
{
    use RelatedFieldTrait;

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_LOOKUP_BEFORE_FIND() => 'beforeLookup',
            (string)EventName::API_LOOKUP_AFTER_FIND() => 'afterLookup'
        ];
    }

    /**
     * Add conditions to Lookup Query.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\QueryInterface $query ORM Query
     * @return void
     */
    public function beforeLookup(Event $event, QueryInterface $query)
    {
        $request = $event->subject()->request;
        $table = $event->subject()->{$event->subject()->name};

        $this->_alterQuery($table, $query, $request);
    }

    /**
     * Alters lookup query and adds ORDER BY clause, WHERE clause
     * if a query string is passed and typeahead fields are defined.
     *
     * Also it adds table JOIN if parent modules are defined.
     *
     * Additionally if any of the defined typeahead fields is a related
     * one, then the WHERE clause condition changes from LIKE to IN and
     * includes the related module's UUIDs that matched the query string.
     *
     * NOTE: There are recursive calls between this method and _getRelatedModuleValues().
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \Cake\Http\ServerRequest $request Request object
     * @return void
     */
    protected function _alterQuery(RepositoryInterface $table, QueryInterface $query, ServerRequest $request)
    {
        $fields = $this->_getTypeaheadFields($table);

        $query->order($this->_getOrderByFields($table, $query, $fields));

        $this->_joinParentTables($table, $query);

        if (empty($fields)) {
            return;
        }

        if (!$request->query('query')) {
            return;
        }

        // add typeahead fields to where clause
        $value = $request->query('query');
        foreach ($fields as $field) {
            $csvField = $this->_getCsvField($field, $table);
            if (!empty($csvField) && 'related' === $csvField->getType()) {
                $values = $this->_getRelatedModuleValues($csvField, $request);
                $query->orWhere([$field . ' IN' => $values]);
            } else {
                // always type-cast fields to string for LIKE clause to work.
                // otherwise for cases where type is integer LIKE value '%123%' will be converted to '0'
                $typeMap = array_combine($fields, array_pad([], count($fields), 'string'));
                $query->typeMap($typeMap);
                $query->orWhere([$field . ' LIKE' => '%' . $value . '%']);
            }
        }
    }

    /**
     * Instantiates and returns a CsvField object of the provided field.
     *
     * @param string $field Field name
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return null|\CsvMigrations\FieldHandlers\CsvField
     */
    protected function _getCsvField($field, RepositoryInterface $table)
    {
        $result = null;
        if (false !== strpos($field, '.')) {
            list(, $field) = explode('.', $field);
        }

        if (empty($field)) {
            return $result;
        }

        $method = 'getFieldsDefinitions';
        if (!method_exists($table, $method) || !is_callable([$table, $method])) {
            return $result;
        }

        $fieldsDefinitions = $table->{$method}();
        if (empty($fieldsDefinitions[$field])) {
            return $result;
        }

        return new CsvField($fieldsDefinitions[$field]);
    }

    /**
     * Returns related module UUIDs matching the query string.
     *
     * NOTE: There are recursive calls between this method and _alterQuery().
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @param \Cake\Http\ServerRequest $request Request object
     * @return array
     */
    protected function _getRelatedModuleValues(CsvField $csvField, ServerRequest $request)
    {
        $table = TableRegistry::get($csvField->getLimit());
        $query = $table->find('list', [
            'keyField' => $table->primaryKey()
        ]);

        // recursive call
        $this->_alterQuery($table, $query, $request);

        $result = $query->toArray();

        $result = !empty($result) ? array_keys($result) : [null];

        return $result;
    }

    /**
     * Modify lookup entities after they have been fetched from the database
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\Datasource\ResultSetDecorator $entities Entities resultset
     * @return void
     */
    public function afterLookup(Event $event, ResultSetDecorator $entities)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $table = $event->subject()->{$event->subject()->name};

        // Properly populate display values for the found entries.
        // This will recurse into related modules and get display
        // values as deep as needed.
        $fhf = new FieldHandlerFactory();
        foreach ($entities as $id => $label) {
            $event->result[$id] = $fhf->renderValue(
                $table,
                $table->getDisplayField(),
                $label,
                ['renderAs' => Setting::RENDER_PLAIN_VALUE_RELATED()]
            );
        }

        $parentModule = $this->_getParentModule($table);
        if ('' === $parentModule) {
            return;
        }

        foreach ($event->result as $id => &$label) {
            $label = $this->_prependParentModule($table->registryAlias(), $parentModule, $id, $label);
        }
    }

    /**
     * Get module's virtual fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    protected function _getVirtualFields(RepositoryInterface $table)
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $table->getRegistryAlias()))->parse();

        return $config->virtualFields;
    }

    /**
     * Updates the provided list of mixed real and virtual fields, so that the final list includes only real fields.
     * This is done by taking into consideration the corresponding section in config.json
     *
     * @param RepositoryInterface $table Table instance
     * @param array $fields List of mixed real and virtual fields
     * @return array
     */
    private function extractVirtualFields(RepositoryInterface $table, array $fields)
    {
        $virtualFields = $this->_getVirtualFields($table);

        $extractedFields = [];
        foreach ($fields as $fieldName) {
            if (isset($virtualFields->{$fieldName})) {
                $extractedFields = array_merge($extractedFields, $virtualFields->{$fieldName});
            } else {
                $extractedFields[] = $fieldName;
            }
        }

        return array_unique($extractedFields);
    }

    /**
     * Get module's type-ahead fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    protected function _getTypeaheadFields(RepositoryInterface $table)
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $table->getRegistryAlias()))->parse();

        $fields = ! empty($config->table->typeahead_fields) ?
            $config->table->typeahead_fields :
            [$table->getDisplayField()];

        // Extract the virtual fields to actual db fields before asking for an alias
        $fields = $this->extractVirtualFields($table, $fields);
        foreach ($fields as $k => $v) {
            $fields[$k] = $table->aliasField($v);
        }

        return $fields;
    }

    /**
     * Get order by fields for lookup Query.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param \Cake\Datasource\QueryInterface $query ORM Query
     * @param array $fields Optional fields to be used in order by clause
     * @return array
     */
    protected function _getOrderByFields(RepositoryInterface $table, QueryInterface $query, array $fields = [])
    {
        $parentModule = $this->_getParentModule($table);
        if ('' === $parentModule) {
            return $this->extractVirtualFields($table, $fields);
        }

        $parentAssociation = null;
        foreach ($table->associations() as $association) {
            if ($association->className() !== $parentModule) {
                continue;
            }
            $parentAssociation = $association;
            break;
        }

        if (is_null($parentAssociation)) {
            return $this->extractVirtualFields($table, $fields);
        }

        $targetTable = $parentAssociation->target();

        // add parent display field to order-by fields
        array_unshift($fields, $targetTable->aliasField($targetTable->displayField()));

        $fields = $this->_getOrderByFields($targetTable, $query, $fields);

        return $this->extractVirtualFields($table, $fields);
    }

    /**
     * Join parent modules.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param \Cake\Datasource\QueryInterface $query ORM Query
     * @return void
     */
    protected function _joinParentTables(RepositoryInterface $table, QueryInterface $query)
    {
        $parentModule = $this->_getParentModule($table);
        if ('' === $parentModule) {
            return;
        }

        $parentAssociation = null;
        foreach ($table->associations() as $association) {
            if ($association->className() !== $parentModule) {
                continue;
            }
            $parentAssociation = $association;
            break;
        }

        if (is_null($parentAssociation)) {
            return;
        }

        $targetTable = $parentAssociation->target();
        $primaryKey = $targetTable->aliasField($parentAssociation->primaryKey());
        $foreignKey = $table->aliasField($parentAssociation->foreignKey());

        // join parent table
        $query->join([
            'table' => $targetTable->table(),
            'alias' => $parentAssociation->name(),
            'type' => 'INNER',
            'conditions' => $foreignKey . ' = ' . $primaryKey . ' OR ' . $foreignKey . ' IS NULL'
        ]);

        $this->_joinParentTables($targetTable, $query);
    }

    /**
     * Returns parent module name for provided Table instance.
     * If parent module is not defined then it returns null.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return string
     */
    protected function _getParentModule(RepositoryInterface $table)
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $table->getRegistryAlias()))->parse();

        return isset($config->parent->module) ? $config->parent->module : '';
    }

    /**
     * Prepend parent module display field to label.
     *
     * @param string $tableName Table name
     * @param string $parentModule Parent module name
     * @param string $id uuid
     * @param string $label Label
     * @return array
     */
    protected function _prependParentModule($tableName, $parentModule, $id, $label)
    {
        $properties = $this->_getRelatedParentProperties(
            $this->_getRelatedProperties($tableName, $id)
        );

        if (empty($properties['dispFieldVal'])) {
            return $label;
        }

        $prefix = $properties['dispFieldVal'] . ' ' . $this->_separator . ' ';

        if (empty($properties['config']['parent']['module']) || empty($properties['id'])) {
            return $prefix . $label;
        }

        $prefix = $this->_prependParentModule(
            $parentModule,
            $properties['config']['parent']['module'],
            $properties['id'],
            $prefix
        );

        return $prefix . $label;
    }
}
