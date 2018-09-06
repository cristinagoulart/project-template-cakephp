<?php

namespace App\Event\Plugin\Menu\View;

use Cake\Datasource\EntityInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Menu\MenuBuilder\MenuItemFactory;

/**
 * trait MenuEntityTrait
 *
 * Facilitates menu construction for common actions around any entity. These are
 * - view
 * - edit
 * - delete
 *
 * @package App\Event\Plugin\Menu\View
 */
trait MenuEntityTrait
{

    /**
     * Creates and returns the menu item for the view action
     *
     * @param EntityInterface $entity Entity to be viewed
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getViewMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $id],
            'icon' => 'eye',
            'label' => __('View'),
            'type' => 'link_button',
            'order' => 100
        ]);
    }

    /**
     * Creates and returns the menu item for the edit action
     *
     * @param EntityInterface $entity Entity to be edited
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getEditMenuItem(EntityInterface $entity, ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $id],
            'icon' => 'pencil',
            'label' => __('Edit'),
            'type' => 'link_button',
            'order' => 110
        ]);
    }

    /**
     * Creates and returns the menu item for the delete action
     *
     * @param EntityInterface $entity Entity to be deleted
     * @param ServerRequest $request Current server request
     * @param bool $useApi Indicates whether to use API or not
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getDeleteMenuItem(EntityInterface $entity, ServerRequest $request, $useApi = false)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');
        $id = $entity->get('id');

        $table = TableRegistry::get($entity->getSource());
        $displayField = $table->getDisplayField();
        $displayName = $entity->has($displayField) ? $entity->get($displayField) : null;

        $config = [
            'url' => ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $id],
            'icon' => 'trash',
            'label' => __('Delete'),
            'type' => 'postlink_button',
            'order' => 120
        ];

        if ($useApi) {
            $config['url']['prefix'] = 'api';
            $config['url']['_ext_'] = '_json';
            $config['type'] = 'link_button';
            $config['dataType'] = 'ajax-delete-record';
        } else {
            $config['confirmMsg'] = __(
                'Are you sure you want to delete {0}?',
                empty(trim($displayName)) ? 'this record' : strip_tags($displayName)
            );
        }

        return MenuItemFactory::createMenuItem($config);
    }
}
