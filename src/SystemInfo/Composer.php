<?php
namespace App\SystemInfo;

/**
 * Composer class
 *
 * This is a helper class that assists with
 * fetching a variety of composer information
 * from the system.
 */
class Composer
{
    /**
     * Get a list of installed composer packages
     *
     * @return mixed[]
     */
    public static function getInstalledPackages(): array
    {
        //
        // Installed composer libraries (from composer.lock file)
        //
        $composerLock = ROOT . DS . 'composer.lock';
        $composer = null;
        if (is_readable($composerLock)) {
            $content = file_get_contents($composerLock);
            $content = $content ?: '';
            $composer = json_decode($content, true);
        }
        $result = !empty($composer['packages']) ? $composer['packages'] : [];

        return $result;
    }

    /**
     * Get a list of match counts
     *
     * This method looks in the list of provided composer
     * packages for the list of provided match words, and
     * returns the list of counts for each match word.
     *
     * @param mixed[] $packages Installed composer packages
     * @param string[] $matchWords List of words to match
     * @return mixed[]
     */
    public static function getMatchCounts(array $packages, array $matchWords): array
    {
        $result = [];
        foreach ($packages as $package) {
            // Concatenate all fields that we'll be matching against
            $matchString = static::getMatchString($package);
            foreach ($matchWords as $word) {
                if (empty($result[$word])) {
                    $result[$word] = 0;
                }
                if (preg_match('/' . $word . '/', $matchString)) {
                    $result[$word]++;
                }
            }
        }

        return $result;
    }

    /**
     * Concatenate matching package fields into a single string
     *
     * @param mixed[] $package Package data
     * @return string
     */
    protected static function getMatchString(array $package): string
    {
        $result = '';

        $keys = ['name', 'description'];
        foreach ($keys as $key) {
            if (!empty($package[$key])) {
                $result .= ' ' . $package[$key];
            }
        }

        return $result;
    }
}
