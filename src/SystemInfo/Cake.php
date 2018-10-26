<?php
namespace App\SystemInfo;

use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * Cake class
 *
 * This is a helper class that assists with
 * fetching a variety of CakePHP information
 * from the system.
 */
class Cake
{
    /**
     * @var string $releasesUrl Base URL to CakePHP releases
     */
    protected static $releasesUrl = 'https://github.com/cakephp/cakephp/releases/tag/';

    /**
     * Get CakePHP version
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return Configure::version();
    }

    /**
     * Get CakePHP version URL
     *
     * This method returns the URL to the release
     * notes of a given version.  If the version
     * is not specified, then the URL to the current
     * CakePHP version will be returned.
     *
     * @param string $version CakePHP version
     * @return string
     */
    public static function getVersionUrl(string $version = null): string
    {
        if (empty($version)) {
            $version = static::getVersion();
        }

        return static::$releasesUrl . $version;
    }

    /**
     * Get the list of loaded CakePHP plugins
     *
     * @return string[]
     */
    public static function getLoadedPlugins(): array
    {
        $result = Plugin::loaded();
        $result = is_array($result) ? $result : [];

        return $result;
    }
}
