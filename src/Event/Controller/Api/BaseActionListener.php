<?php
namespace App\Event\Controller\Api;

use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use Cake\View\View;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Utility\FileUpload;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseActionListener implements EventListenerInterface
{
    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    /**
     * Include menus identifier
     */
    const FLAG_INCLUDE_MENUS = 'menus';

    /**
     * Property name for menu items
     */
    const MENU_PROPERTY_NAME = '_Menus';

    /**
     * FieldHandlerFactory instance.
     *
     * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $factory = null;

    /**
     * View instance.
     *
     * @var \Cake\View\View
     */
    private $view = null;

    /**
     * FileUpload instance.
     *
     * @var \CsvMigrations\Utility\FileUpload
     */
    private $fileUpload = null;

    /**
     * Current controller name.
     *
     * @var string
     */
    private $controllerName = '';

    /**
     * Fetch and attach associated files to provided entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return void
     */
    protected function attachFiles(EntityInterface $entity, RepositoryInterface $table) : void
    {
        $fileUpload = $this->getFileUpload($table);

        foreach ($this->getFileAssociations($table) as $association) {
            $conditions = $association->getConditions();
            if (! array_key_exists('model_field', $conditions)) {
                continue;
            }

            $entity->set(
                $conditions['model_field'],
                $fileUpload->getFiles($conditions['model_field'], $entity->get($table->getPrimaryKey()))
            );
        }
    }

    /**
     * Method responsible for retrieving current table's file associations.
     *
     * @param  \Cake\Datasource\RepositoryInterface $table Table instance
     * @return \Cake\ORM\Association[]
     */
    protected function getFileAssociations(RepositoryInterface $table) : array
    {
        $result = [];
        foreach ($table->associations() as $association) {
            if (FileUpload::FILES_STORAGE_NAME !== $association->className()) {
                continue;
            }

            $result[] = $association;
        }

        return $result;
    }

    /**
     * Convert Entity resource values to strings.
     * Temporary fix for bug with resources and json_encode() (see link).
     *
     * @param  \Cake\Datasource\EntityInterface $entity Entity
     * @return void
     * @link https://github.com/cakephp/cakephp/issues/9658
     */
    protected function resourceToString(EntityInterface $entity) : void
    {
        foreach (array_keys($entity->toArray()) as $field) {
            /**
             * handle belongsTo associated data
             *
             * @deprecated since qobo/cakephp-csv-migrations v12.1.0 - We currently do not support inclusion of
             * associated data on API responses. The only exception being associated files, but this is handled
             * within the field-handler factory call below.
             */
            if ($entity->get($field) instanceof EntityInterface) {
                trigger_error(sprintf('Associated data in API responses are not supported.'), E_USER_DEPRECATED);
                $this->resourceToString($entity->{$field});
            }

            /**
             * handle hasMany associated data
             *
             * @deprecated since qobo/cakephp-csv-migrations v12.1.0 - We currently do not support inclusion of
             * associated data on API responses. The only exception being associated files, but this is handled
             * within the field-handler factory call below.
             */
            if (is_array($entity->get($field)) && ! empty($entity->get($field))) {
                trigger_error(sprintf('Associated data in API responses are not supported.'), E_USER_DEPRECATED);
                foreach ($entity->get($field) as $associatedEntity) {
                    if ($associatedEntity instanceof EntityInterface) {
                        $this->resourceToString($associatedEntity);
                    }
                }
            }

            if (is_resource($entity->get($field))) {
                $entity->set($field, stream_get_contents($entity->get($field)));
            }
        }
    }

    /**
     * Method that renders Entity values through Field Handler Factory.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $fields Fields to prettify
     * @return void
     */
    protected function prettify(EntityInterface $entity, RepositoryInterface $table, array $fields = []) : void
    {
        $fields = empty($fields) ? array_keys($entity->toArray()): $fields;

        /**
         * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
         */
        $factory = $this->getFieldHandlerFactory();

        foreach ($fields as $field) {
            /**
             * handle belongsTo associated data
             *
             * @deprecated since qobo/cakephp-csv-migrations v12.1.0 - We currently do not support inclusion of
             * associated data on API responses. The only exception being associated files, but this is handled
             * within the field-handler factory call below.
             */
            if ($entity->get($field) instanceof EntityInterface) {
                trigger_error(sprintf('Associated data in API responses are not supported.'), E_USER_DEPRECATED);

                $tableName = $table->association($entity->get($field)->getSource())->className();
                $this->prettify($entity->{$field}, $tableName);
            }

            /**
             * handle hasMany associated data
             *
             * @deprecated since qobo/cakephp-csv-migrations v12.1.0 - We currently do not support inclusion of
             * associated data on API responses. The only exception being associated files, but this is handled
             * within the field-handler factory call below.
             */
            if (is_array($entity->get($field)) && ! empty($entity->get($field))) {
                trigger_error(sprintf('Associated data in API responses are not supported.'), E_USER_DEPRECATED);

                foreach ($entity->get($field) as $associatedEntity) {
                    if (! $associatedEntity instanceof EntityInterface) {
                        continue;
                    }

                    list(, $associationName) = pluginSplit($associatedEntity->getSource());
                    $tableName = $table->association($associationName)->className();
                    $this->prettify($associatedEntity, $tableName);
                }
            }

            $entity->set($field, $factory->renderValue($table, $field, $entity->get($field), ['entity' => $entity]));
        }
    }

    /**
     * Query order clause getter.
     *
     * This is a temporary solution for multi-column sort support,
     * until crud plugin adds relevant functionality.
     * @link https://github.com/FriendsOfCake/crud/issues/522
     * @link https://github.com/cakephp/cakephp/issues/7324
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return array
     */
    protected function getOrderClause(ServerRequestInterface $request, RepositoryInterface $table = null) : array
    {
        if (! $request->getQuery('sort')) {
            return [];
        }

        $columns = explode(',', $request->getQuery('sort'));

        if (is_null($table)) {
            return array_fill_keys($columns, $request->getQuery('direction'));
        }

        foreach ($columns as $k => $v) {
            $columns[$k] = $table->aliasField($v);
        }

        return array_fill_keys($columns, $request->getQuery('direction'));
    }

    /**
     * Method that retrieves and attaches menu elements to API response.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return void
     */
    protected function attachMenu(EntityInterface $entity, RepositoryInterface $table, array $user) : void
    {
        $data = [
            'plugin' => false,
            'controller' => $this->getControllerName($table),
            'displayField' => $table->getDisplayField(),
            'entity' => $entity,
            'user' => $user
        ];

        $entity->set(static::MENU_PROPERTY_NAME, $this->getView()->element('Module/Menu/index_actions', $data));
    }

    /**
     * Method that retrieves and attaches menu elements to API response.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @param array $data for extra fields like origin Id
     * @return void
     */
    protected function attachRelatedMenu(EntityInterface $entity, RepositoryInterface $table, array $user, array $data) : void
    {
        $data += [
            'plugin' => false,
            'controller' => $this->getControllerName($table),
            'displayField' => $table->getDisplayField(),
            'entity' => $entity,
            'user' => $user
        ];

        $entity->set(static::MENU_PROPERTY_NAME, $this->getView()->element('Module/Menu/related_actions', $data));
    }

    /**
     * FieldHandlerFactory instance getter.
     *
     * @return \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private function getFieldHandlerFactory() : FieldHandlerFactory
    {
        if (null === $this->factory) {
            $this->factory = new FieldHandlerFactory();
        }

        return $this->factory;
    }

    /**
     * View instance getter.
     *
     * @return \Cake\View\View
     */
    private function getView() : View
    {
        if (null === $this->view) {
            $this->view = new View();
        }

        return $this->view;
    }

    /**
     * FileUpload instance getter.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return \CsvMigrations\Utility\FileUpload
     */
    private function getFileUpload(RepositoryInterface $table) : FileUpload
    {
        if (null === $this->fileUpload) {
            $this->fileUpload = new FileUpload($table);
        }

        return $this->fileUpload;
    }

    /**
     * Current controller name getter.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return string
     */
    private function getControllerName(RepositoryInterface $table) : string
    {
        if ('' === $this->controllerName) {
            $this->controllerName = App::shortName(get_class($table), 'Model/Table', 'Table');
        }

        return $this->controllerName;
    }
}
