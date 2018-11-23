<?php

namespace App\Access;

use Cake\Core\Configure;
use Cake\Routing\Router;
use InvalidArgumentException;
use RolesCapabilities\CapabilityTrait as DefaultCapabilityTrait;

/**
 * Trait CapabilityTrait
 * Overwrites the CapabilityTrait provided by roles-rensponsibilities plugin so that Features are taken into consideration.
 * Requests for access to routes that belong any disabled Feature are being rejected.
 *
 * @package App\Access
 */
trait CapabilityTrait
{
    use DefaultCapabilityTrait {
        DefaultCapabilityTrait::_checkAccess as defaultCheckAccess;
    }

    /**
     * Returns true only and only if the provided user has access to the provided URL.
     *
     * @param string|array $url URL to be checked
     * @param mixed[] $user User information
     * @return bool True if user has access to the provided URL
     */
    protected function _checkAccess($url, array $user): bool
    {
        $stringUrl = is_array($url) ? Router::url($url) : $url;

        if (! is_string($stringUrl)) {
            throw new InvalidArgumentException();
        }

        foreach ((array)Configure::read('Menu.routes.blacklist') as $route) {
            if (0 === strpos($stringUrl, $route)) {
                return false;
            }
        }

        return $this->defaultCheckAccess($this->parseUrl($url), $user);
    }

    /**
     * Parses menu item URL.
     *
     * @param array|string $url Menu item URL
     * @return mixed[]
     */
    private function parseUrl($url): array
    {
        if (!is_string($url)) {
            return $url;
        }

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
