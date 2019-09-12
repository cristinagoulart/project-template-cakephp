<?php

namespace App\Event\Plugin\Menu\View;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Routing\Router;
use InvalidArgumentException;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemContainerInterface;
use Menu\MenuBuilder\MenuItemInterface;
use RolesCapabilities\CapabilityTrait;

class AccessMenuListener implements EventListenerInterface
{
    use CapabilityTrait;

    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => [
                'callable' => 'getMenuItems',
                'priority' => 100
            ]
        ];
    }

    /**
     * Disables all menu items that the provided user doesn't have access to
     *
     * @param \Cake\Event\Event $event Event object
     * @param string $name Menu name
     * @param mixed[] $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param mixed[] $modules Modules to fetch menu items for
     * @param \Menu\MenuBuilder\MenuInterface|null $menu Menu object to be updated
     * @return void
     */
    public function getMenuItems(Event $event, string $name, array $user, bool $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null): void
    {
        if ($menu === null) {
            return;
        }

        $this->disableUnauthorisedItems($menu, $user);
        $event->setResult($menu);
    }

    /**
     * Lazy checking if the specified user has access to the specified menu items.
     * If not, the menu item will be marked as disabled.
     * Recursively, applies the checks to all levels starting from the specified container.
     *
     * @param \Menu\MenuBuilder\MenuItemContainerInterface $container Starting menu container
     * @param mixed[] $user Current user
     * @return void
     */
    private function disableUnauthorisedItems(MenuItemContainerInterface $container, array $user): void
    {
        /**
        * @var \Menu\MenuBuilder\MenuItemInterface $item
        */
        foreach ($container->getMenuItems() as $item) {
            $item->disableIf(function (MenuItemInterface $item) use ($user) {
                return !$this->checkAccess($item->getUrl(), $user);
            });

            $this->disableUnauthorisedItems($item, $user);
        }
    }

    /**
     * Returns true only and only if the provided user has access to the provided URL.
     *
     * @param string|array $url URL to be checked
     * @param mixed[] $user User information
     * @return bool True if user has access to the provided URL
     */
    private function checkAccess($url, array $user): bool
    {
        $stringUrl = is_array($url) ? Router::url($url) : $url;

        if (! is_string($stringUrl)) {
            throw new InvalidArgumentException(sprintf(
                'Failed to convert url into a string: %s',
                json_encode($url, JSON_PRETTY_PRINT)
            ));
        }

        foreach ((array)Configure::read('Menu.routes.blacklist') as $route) {
            if (0 === strpos($stringUrl, $route)) {
                return false;
            }
        }

        return $this->_checkAccess($this->parseUrl($stringUrl), $user);
    }

    /**
     * Parses menu item URL.
     *
     * @param string $url Menu item URL
     * @return mixed[]
     */
    private function parseUrl(string $url): array
    {
        $fullBaseUrl = Router::fullBaseUrl();

        // strip out full base URL from menu item's URL.
        if (false !== strpos($url, $fullBaseUrl)) {
            $url = str_replace($fullBaseUrl, '', $url);
        }

        if (0 !== strpos($url, '/')) {
            $url = '/' . $url;
        }

        return Router::getRouteCollection()->parse($url);
    }
}
