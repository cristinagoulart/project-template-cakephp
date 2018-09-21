<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;

class ModuleIndexListener implements EventListenerInterface
{
    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems'
        ];
    }

    /**
     * Method that returns menu for Module index page
     *
     * @param Event $event Event object
     * @param string $name Menu name
     * @param array $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param array $modules Modules to fetch menu items for
     * @param MenuInterface|null $menu Menu object to be updated
     * @return void
     */
    public function getMenuItems(Event $event, $name, array $user, $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null)
    {
        if ($name !== MenuName::MODULE_INDEX_TOP) {
            return;
        }

        $request = Router::getRequest();
        $menu->addMenuItem($this->getBatchMenuItem($request));
        $menu->addMenuItem($this->getImportMenuItem($request));
        $menu->addMenuItem($this->getAddMenuItem($request));

        if ($request->param('controller') == 'ScheduledJobs') {
            $menu->addMenuItem($this->getDelLogItem());
        }

        $event->setResult($menu);
    }

    /**
     * Creates and returns the menu item for the batch operations
     *
     * @param Cake\Http\ServerRequest $request Current server request
     * @return Menu\MenuBuilder\MenuItemInterface
     */
    private function getBatchMenuItem(ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');

        $batchItem = MenuItemFactory::createMenuItem([
            'type' => 'button',
            'icon' => 'bars',
            'label' => __('Batch'),
            'order' => 1,
            'attributes' => [
                'id' => 'batch-button',
                'disabled' => 'disabled',
            ],
        ]);
        $batchItem->disableIf(function () {
            return !Configure::read('CsvMigrations.batch.active');
        });

        $batchItem->addMenuItem(MenuItemFactory::createMenuItem([
            'icon' => 'pencil',
            'label' => __('Edit'),
            'order' => 1,
            'attributes' => [
                'data-batch' => true,
                'data-batch-url' => Router::url(['plugin' => $plugin, 'controller' => $controller, 'action' => 'batch', 'edit']),
            ],
        ]));

        $batchItem->addMenuItem(MenuItemFactory::createMenuItem([
            'icon' => 'trash',
            'label' => __('Delete'),
            'order' => 10,
            'attributes' => [
                'data-batch' => true,
                'data-batch-url' => Router::url(['plugin' => $plugin, 'controller' => $controller, 'action' => 'batch', 'delete']),
                'data-batch-confirm' => __('Are you sure you want to delete the selected records?'),
            ]
        ]));

        return $batchItem;
    }

    /**
     * Creates and returns the menu item for the import action
     *
     * @param Cake\Http\ServerRequest $request Current server request
     * @return Menu\MenuBuilder\MenuItemInterface
     */
    private function getImportMenuItem(ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');

        return MenuItemFactory::createMenuItem([
            'url' => ['plugin' => $plugin, 'controller' => $controller, 'action' => 'import'],
            'icon' => 'upload',
            'label' => __('Import'),
            'type' => 'link_button',
            'order' => 10,
        ]);
    }

    /**
     * Creates and returns the menu item for the add action
     *
     * @param Cake\Http\ServerRequest $request Current server request
     * @return Menu\MenuBuilder\MenuItemInterface
     */
    private function getAddMenuItem(ServerRequest $request)
    {
        $plugin = $request->param('plugin');
        $controller = $request->param('controller');

        return MenuItemFactory::createMenuItem([
            'url' => ['plugin' => $plugin, 'controller' => $controller, 'action' => 'add'],
            'icon' => 'plus',
            'label' => __('Add'),
            'type' => 'link_button',
            'order' => 20,
        ]);
    }

    /**
     * Delete logs from Scheduler job page
     *
     * @return Menu\MenuBuilder\MenuItemInterface
     */
    private function getDelLogItem()
    {
        $age = Configure::read('ScheduledLog.stats.age');

        return MenuItemFactory::createMenuItem([
            'url' => ['plugin' => false, 'controller' => 'ScheduledJobLogs', 'action' => 'gc'],
            'icon' => 'trash',
            'label' => __('Delete old'),
            'confirmMsg' => __('Are you sure? This action will delete all the Scheduled logs older than ' . ltrim($age, '-') . '.'),
            'type' => 'postlink_button',
            'order' => 20,
        ]);
    }
}
