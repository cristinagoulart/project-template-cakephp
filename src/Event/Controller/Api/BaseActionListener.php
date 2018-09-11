<?php
namespace App\Event\Controller\Api;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
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
     * File association class name
     */
    const FILE_CLASS_NAME = 'Burzum/FileStorage.FileStorage';

    /**
     * Current module fields list which are associated with files
     *
     * @var array
     */
    protected $_fileAssociationFields = [];

    /**
     * An instance of Field Handler Factory
     *
     * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $factory;

    /**
     * Move associated files under the corresponding entity property
     * and unset association property.
     *
     * Entity argument:
     *
     * ```
     * \Cake\ORM\Entity $object {
     *     'file' => null,
     *     'file_file_storage_file_storage' => [
     *         0 => \Burzum\FileStorage\Model\Entity\FileStorage $object,
     *         1 => \Burzum\FileStorage\Model\Entity\FileStorage $object
     *     ]
     * }
     * ```
     *
     * Becomes:
     *
     * ```
     * \Cake\ORM\Entity $object {
     *     'file' => [
     *         0 => \Burzum\FileStorage\Model\Entity\FileStorage $object,
     *         1 => \Burzum\FileStorage\Model\Entity\FileStorage $object
     *     ]
     * }
     * ```
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return void
     */
    protected function _restructureFiles(EntityInterface $entity, RepositoryInterface $table)
    {
        foreach ($this->_getFileAssociationFields($table) as $association => $target) {
            $source = Inflector::underscore($association);

            $entity->set($target, $entity->get($source));
            $entity->unsetProperty($source);
            $this->_attachThumbnails($entity->get($target), $table);
        }
    }

    /**
     * Attach image file thumbnails into the entity.
     *
     * @param array $images Entity images
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     */
    protected function _attachThumbnails(array $images, Table $table)
    {
        if (empty($images)) {
            return;
        }

        $hashes = Configure::read('FileStorage.imageHashes.file_storage');
        $fileUpload = new FileUpload($table);
        $extensions = $fileUpload->getImgExtensions();

        // append thumbnails
        foreach ($images as &$image) {
            // skip  non-image files
            if (!in_array($image->extension, $extensions)) {
                continue;
            }

            $image->set('thumbnails', []);
            $path = dirname($image->path) . '/' . basename($image->path, $image->extension);
            foreach ($hashes as $name => $hash) {
                $thumbnailPath = $path . $hash . '.' . $image->extension;
                // if thumbnail does not exist, provide the path to the original image
                $thumbnailPath = !file_exists(WWW_ROOT . $thumbnailPath) ? $image->path : $thumbnailPath;
                $image->thumbnails[$name] = $thumbnailPath;
            }
        }
    }

    /**
     * Method that generates property name for belongsTo and HasOne associations.
     *
     * @param  string $name Association name
     * @return string
     */
    protected function _associationPropertyName($name)
    {
        list(, $name) = pluginSplit($name);

        return Inflector::underscore(Inflector::singularize($name));
    }

    /**
     * Method responsible for retrieving current Table's file associations
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociations(Table $table)
    {
        $result = [];

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }

            $result[] = $association->name();
        }

        return $result;
    }

    /**
     * Method responsible for retrieving file associations field names
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociationFields(Table $table)
    {
        $result = [];

        if (!empty($this->_fileAssociationFields)) {
            return $this->_fileAssociationFields;
        }

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }
            $result[$association->name()] = $association->conditions()['model_field'];
        }

        $this->_fileAssociationFields = $result;

        return $this->_fileAssociationFields;
    }

    /**
     * Convert Entity resource values to strings.
     * Temporary fix for bug with resources and json_encode() (see link).
     *
     * @param  \Cake\ORM\Entity $entity Entity
     * @return void
     * @link   https://github.com/cakephp/cakephp/issues/9658
     */
    protected function _resourceToString(Entity $entity)
    {
        $fields = array_keys($entity->toArray());
        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $this->_resourceToString($entity->{$field});
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }

                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    $this->_resourceToString($associatedEntity);
                }
            }

            if (is_resource($entity->{$field})) {
                $entity->{$field} = stream_get_contents($entity->{$field});
            }
        }
    }

    /**
     * Method that renders Entity values through Field Handler Factory.
     *
     * @param  \Cake\ORM\Entity       $entity    Entity instance
     * @param  \Cake\ORM\Table|string $table     Table instance
     * @param  array                 $fields    Fields to prettify
     * @return void
     */
    protected function _prettify(Entity $entity, $table, array $fields = [])
    {
        if (!$this->factory instanceof FieldHandlerFactory) {
            $this->factory = new FieldHandlerFactory();
        }
        if (empty($fields)) {
            $fields = array_keys($entity->toArray());
        }

        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $tableName = $table->association($entity->{$field}->source())->className();
                $this->_prettify($entity->{$field}, $tableName);
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }
                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    list(, $associationName) = pluginSplit($associatedEntity->source());
                    $tableName = $table->association($associationName)->className();
                    $this->_prettify($associatedEntity, $tableName);
                }
            }

            $entity->{$field} = $this->factory->renderValue($table, $field, $entity->{$field}, ['entity' => $entity]);
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
    protected function getOrderClause(ServerRequestInterface $request, RepositoryInterface $table = null)
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
     * @param \Cake\Datasource\ResultSetInterface $resultSet ResultSet object
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return void
     */
    protected function attachMenu(ResultSetInterface $resultSet, RepositoryInterface $table, array $user)
    {
        $view = new View();
        $controllerName = App::shortName(get_class($table), 'Model/Table', 'Table');

        foreach ($resultSet as $entity) {
            $entity->set(static::MENU_PROPERTY_NAME, $view->element('Module/Menu/index_actions', [
                'plugin' => false,
                'controller' => $controllerName,
                'displayField' => $table->getDisplayField(),
                'entity' => $entity,
                'user' => $user
            ]));
        }
    }

    /**
     * Method that retrieves and attaches menu elements to API response.
     *
     * @param \Cake\Datasource\ResultSetInterface $resultSet ResultSet object
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @param array $data for extra fields like origin Id
     * @return void
     */
    protected function attachRelatedMenu(ResultSetInterface $resultSet, RepositoryInterface $table, array $user, array $data)
    {
        $view = new View();
        $controllerName = App::shortName(get_class($table), 'Model/Table', 'Table');

        foreach ($resultSet as $entity) {
            $entity->set(static::MENU_PROPERTY_NAME, $view->element('Module/Menu/related_actions', [
                'plugin' => false,
                'controller' => $controllerName,
                'displayField' => $table->getDisplayField(),
                'entity' => $entity,
                'user' => $user,
                'associationController' => $data['associationController'],
                'associationName' => $data['associationName'],
                'associationId' => $data['associationId'],
            ]));
        }
    }
}
