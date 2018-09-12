<?php

namespace App\Event\Plugin\Menu\View;

use App\Feature\Config;
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
        'order' => 0,
        'target' => '_self',
        'children' => [],
        'desc' => ''
    ];

    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
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

        $result = $this->applyModuleDefaults($module, $result);

        return $result;
    }

    /**
     * Normalises the provided array menu items for the given module
     * Part of the normalisation is to merge duplicated labels, recursively
     *
     * @param array $items Menu items
     * @return array
     */
    protected function normalizeMenuItems(array $items)
    {
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

    /**
     * Applies the module defaults on the provided menu item
     *
     * @param string $module Module's name
     * @param array $items List of menu items
     * @return array The provided list of menu items including the defaults
     */
    private function applyModuleDefaults($module, array $items)
    {
        // merge item properties with defaults
        $func = function (&$item, $k) use (&$func, $module) {
            if (!empty($item['children'])) {
                array_walk($item['children'], $func);
            }

            $item = array_merge($this->getModuleDefaults($module), $item);
        };
        array_walk($items, $func);

        return $items;
    }

    /**
     * Returns the default values for the specified module.
     * The default icon is provided with the following lookup order
     * - table icon
     * - default icon
     *
     * @param string $module Module's name
     * @return array The defaults
     * @throws \Exception
     */
    private function getModuleDefaults($module)
    {
        return array_merge($this->defaults, [
            'icon' => $this->getModuleIcon($module)
        ]);
    }

    /**
     * Provides an alternative icon in case the menu item was blank.
     * Here is order:
     * - table icon
     * - menu default icon
     *
     * @param string $module The module name
     * @return string
     * @throws \Exception
     */
    private function getModuleIcon($module)
    {
        // Table icon
        $moduleConfig = new ModuleConfig(ConfigType::MODULE(), $module);
        $config = json_decode(json_encode($moduleConfig->parse()), true);
        if (!empty($config) && !empty($config['table']['icon'])) {
            return $config['table']['icon'];
        }

        // Menu default icon
        return $icon = Configure::read('Menu.default_menu_item_icon');
    }
}
