<?php
namespace App\ORM;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\User;
use RolesCapabilities\Access\AccessFactory;
use Webmozart\Assert\Assert;

final class PermissionsFormatter
{
    /**
     * Prettifies ResultSet for UI purposes. It includes html tags.
     *
     * @param \Cake\Collection\CollectionInterface $results ResultSet
     * @return \Cake\Collection\CollectionInterface
     */
    public function __invoke(CollectionInterface $results) : CollectionInterface
    {
        return $results->map(function (EntityInterface $entity) {
            static $table = null;
            if (null === $table) {
                $table = TableRegistry::getTableLocator()->get($entity->getSource());
            }

            $primaryKey = $table->getPrimaryKey();
            Assert::string($primaryKey);

            if (null === $entity->get($primaryKey)) {
                $primaryKey = $table->aliasField($primaryKey);
            }

            if (null === $entity->get($primaryKey)) {
                return $entity;
            }

            Assert::stringNotEmpty($entity->get($primaryKey));

            $entity['_permissions'] = self::getPermissions($entity->get($primaryKey), $table);

            return $entity;
        });
    }

    /**
     * Attaches permission information to the ResultSet.
     *
     * @param string $id Entity ID
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    private static function getPermissions(string $id, Table $table) : array
    {
        static $factory = null;
        if (null === $factory) {
            $factory = new AccessFactory();
        }

        list($plugin, $controller) = pluginSplit($table->getAlias());

        $urls = [
            'view' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id],
            'edit' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id],
            'delete' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $id]
        ];

        array_walk($urls, function (&$item, $key) use ($factory) {
            $className = sprintf('\App\Controller\%sController', $item['controller']);
            $item = $factory->hasAccess($item, User::getCurrentUser()) && method_exists($className, $item['action']);
        });

        return $urls;
    }
}
