<?php

namespace App\SystemInfo;

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Project class
 *
 * This is a helper class that assists with
 * fetching a variety of project information
 * from the system.
 */
class Project
{
    /**
     * @var string $defaultVersion Version string fallback
     */
    protected static $defaultVersion = 'N/A';

    /**
     * @var string $defaultUrl Url string fallback
     */
    protected static $defaultUrl = 'https://github.com/QoboLtd/project-template-cakephp';

    /**
     * Get project name
     *
     * This method returns a human-friendly (if
     * possible) project name for showing to users
     * in the footer, email, etc.
     *
     * @return string project name
     */
    public static function getName(): string
    {
        // Use PROJECT_NAME environment variable or project folder name
        $projectName = getenv('PROJECT_NAME') ?: basename(ROOT);

        return $projectName;
    }

    /**
     * Get project URL
     *
     * This method returns the root URL of the
     * project.
     *
     * @return string
     */
    public static function getUrl(): string
    {
        $result = static::$defaultUrl;

        $url = getenv('PROJECT_URL');
        if (!empty($url)) {
            return $url;
        }

        $url = Router::fullBaseUrl();
        if (!empty($url)) {
            return $url;
        }

        return $result;
    }

    /**
     * Get project version for display purposes
     *
     * This method returns a human-friendly (if
     * possible) version of the system for showing
     * to users in the footer, emails, etc.
     *
     * The versions are read from a variety of sources
     * like environment variables, git history, etc.,
     * with a fallback on a pre-defined value.
     *
     * @return string
     */
    public static function getDisplayVersion(): string
    {
        $result = static::$defaultVersion;

        $version = getenv('PROJECT_VERSION');
        if (!empty($version)) {
            return $version;
        }

        $version = getenv('API_PROJECT_VERSION');
        if (!empty($version)) {
            return $version;
        }

        $version = getenv('GIT_BRANCH');
        if (!empty($version)) {
            return $version;
        }

        $version = Git::getCurrentHash();
        if (!empty($version)) {
            return $version;
        }

        return $result;
    }

    /**
     * Get build versions
     *
     * Return a list of build versions, including the following:
     *
     * * current - the latest attempted build version
     * * deployed - the latest successful build version
     * * previous - the previously attempted build version
     *
     * If any of the build versions is not known, the pre-defined
     * default is returned instead.
     *
     * @return mixed[]
     */
    public static function getBuildVersions(): array
    {
        $result = [];

        // Read build/version* files or use N/A as fallback
        $versions = [
            'current' => ROOT . DS . 'build' . DS . 'version',
            'deployed' => ROOT . DS . 'build' . DS . 'version.ok',
            'previous' => ROOT . DS . 'build' . DS . 'version.bak',
        ];
        foreach ($versions as $version => $file) {
            $result[$version] = static::$defaultVersion;
            if (is_readable($file)) {
                $result[$version] = file_get_contents($file);
            }
        }

        return $result;
    }

    /**
     *  Get project logo
     *
     * @param string $logoSize of logo - mini or large
     * @return string HTML img tag with project logo
     */
    public static function getLogo(string $logoSize = ''): string
    {
        $logoSize = $logoSize ?: 'mini';
        $logo = Configure::read('Theme.logo.' . $logoSize);

        return $logo;
    }

    /**
     * Get project copyright
     *
     * @return string
     */
    public static function getCopyright(): string
    {
        $result = 'Copyright &copy; ' . date('Y') . ' ' . static::getName() . '. All rights reserved.';

        return $result;
    }
}
