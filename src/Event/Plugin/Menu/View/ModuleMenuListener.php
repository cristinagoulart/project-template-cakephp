<?php

namespace App\Event\Plugin\Menu\View;

use App\Feature\Factory as FeatureFactory;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

class ModuleMenuListener implements EventListenerInterface
{
    /**
     * Menu item defaults.
     *
     * @var array
     */
    protected $defaults = [
        'url' => '#',
        'label' => 'Undefined',
        'icon' => 'circle-o',
        'order' => 0,
        'target' => '_self',
        'children' => [],
        'desc' => ''
    ];

    /**
     * @inheritdoc
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems',
        ];
    }

    /**
     * Method that updates the provided Menu to include links defined menus.json files
     *
     * @param Event $event Event object
     * @param string $name Menu name
     * @param array $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param array $modules Modules to fetch menu items for
     * @param MenuInterface|null $menu Menu object to be updated
     * @return void
     * @throws \Exception
     */
    public function getMenuItems(
        Event $event,
        $name,
        array $user,
        $fullBaseUrl = false,
        array $modules = [],
        MenuInterface $menu = null
    ) {
        if (empty($modules)) {
            $modules = Utility::findDirs(Configure::readOrFail('CsvMigrations.modules.path'));

            $menuItems = $this->normalizeMenuItems($this->getModulesMenuItems($modules, $name));
            foreach ($menuItems as $menuItem) {
                $menu->addMenuItem(MenuItemFactory::createMenuItem($menuItem));
            }

            $event->setResult($menu);
        } else {
            $menuItems = $this->getModulesMenuItems($modules, $name);
            $event->setResult($menuItems);
        }
    }

    /**
     * Menu links getter.
     *
     * @param array $modules Modules list
     * @param string $menuName Menu name
     * @return array
     * @throws \Exception
     */
    protected function getModulesMenuItems(array $modules, $menuName)
    {
        if (empty($modules)) {
            return [];
        }

        $result = [];
        foreach ($modules as $module) {
            $feature = FeatureFactory::get('Module' . DS . $module);
            if (!$feature->isActive()) {
                continue;
            }

            $links = $this->getModuleMenuItems($module, $menuName);
            $result = array_merge($result, $links);
        }

        return $result;
    }

    /**
     * Module links getter.
     *
     * @param string $module Module name
     * @param string $menuName Menu name
     * @return array
     * @throws \Exception
     */
    protected function getModuleMenuItems($module, $menuName)
    {
        $moduleConfig = new ModuleConfig(ConfigType::MENUS(), $module);
        $config = json_decode(json_encode($moduleConfig->parse()), true);

        if (empty($config[$menuName])) {
            return [];
        }

        $result = [];
        foreach ($config[$menuName] as $item) {
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Menu items normalization method.
     *
     * @param array $items Menu items
     * @return array
     */
    protected function normalizeMenuItems(array $items)
    {
        // merge item properties with defaults
        $func = function (&$item, $k) use (&$func) {
            if (!empty($item['children'])) {
                array_walk($item['children'], $func);
            }

            $item = array_merge($this->defaults, $item);
        };
        array_walk($items, $func);

        // merge duplicated labels recursively
        $result = [];
        foreach ($items as $item) {
            if (!array_key_exists($item['label'], $result)) {
                $result[$item['label']] = $item;
                continue;
            }

            $result[$item['label']]['children'] = array_merge_recursive(
                $item['children'],
                $result[$item['label']]['children']
            );
        }

        return $result;
    }
}
