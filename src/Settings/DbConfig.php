<?php

namespace App\Settings;

use Cake\Cache\Cache;
use Cake\Core\Configure\ConfigEngineInterface;
use Cake\Database\Exception;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use RuntimeException;

class DbConfig implements ConfigEngineInterface
{

    private $scope;
    private $context;

    /**
     * Set DbConfig to return user settings as default
     * @param string $scope   User, App, (Os, Env ...)
     * @param string $context depent on the scope, the context can be uuid, string, integer, etc.
     */
    public function __construct(string $scope = 'app', string $context = 'app')
    {
        $this->scope = $scope;
        $this->context = $context;
    }

    /**
     * @param string $key Table name with the settings
     * @return array
     */
    public function read($key)
    {
        $query = TableRegistry::getTableLocator()->get($key);
        $cacheKey = $this->scope . '_' . $this->context;
        $config = Cache::read($cacheKey, 'settings');

        if ($config !== false) {
            return $config;
        }

        try {
            $items = $query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
                          ->where(['scope' => $this->scope, 'context' => $this->context])
                          ->toArray();
            $items = $this->decode($items);
        } catch (Exception $e) {
            return [];
        }

        $config = Hash::expand($items);
        Cache::write($cacheKey, $config, 'settings');

        return $config;
    }

    /**
     * @param string $key Table name
     * @param array $data Data to dump.
     * @return bool
     * @throws \Exception
     */
    public function dump($key, array $data): bool
    {
        if ($key || $data) {
            throw new RuntimeException('Not implemented');
        }

        return false;
    }

    /**
     * Attempts to JSON decode each one of the provided items.
     *
     * @param array[] $items
     * @return array[]
     */
    private function decode(array $items): array
    {
        foreach ($items as $key => $val) {
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            $items[$key] = $decoded;
        }

        return $items;
    }
}
