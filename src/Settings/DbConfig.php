<?php
namespace App\Settings;

use Cake\Core\Configure\ConfigEngineInterface;
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
        $query = TableRegistry::get($key);
        // App level costum settings
        try {
            $data = $query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
                          ->where(['scope' => $this->scope, 'context' => $this->context])
                          ->toArray();
        } catch (\Cake\Database\Exception $e) {
            return [];
        }

        $config = Hash::expand($data);

        return $config;
    }

    /**
     * @param string $key Table name
     * @param array $data Data to dump.
     * @return bool
     * @throws \Exception
     */
    public function dump($key, array $data) : bool
    {
        if ($key || $data) {
            throw new RuntimeException('Not implemented');
        }

        return false;
    }
}
