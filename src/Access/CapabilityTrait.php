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
     * @param array $user User information
     * @return bool True if user has access to the provided URL
     */
    protected function _checkAccess($url, $user)
    {
        $stringUrl = null;
        if (is_array($url)) {
            $stringUrl = Router::url($url);
        } elseif (is_string($url)) {
            $stringUrl = $url;
        } else {
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
     * @return string
     */
    private function parseUrl($url)
    {
        if (!is_string($url)) {
            return $url;
        }

        $fullBaseUrl = Router::fullBaseUrl();

        // strip out full base URL from menu item's URL.
        if (false !== strpos($url, $fullBaseUrl)) {
            $url = str_replace($fullBaseUrl, '', $url);
        }

        return Router::parse($url);
    }
}
