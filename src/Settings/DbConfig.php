<?php
namespace App\Settings;

use Cake\Core\Configure\ConfigEngineInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;

class DbConfig implements ConfigEngineInterface
{
    /**
     * @param string $key Table name with the settings
     * @return array
     */
    public function read($key)
    {
        // if the table $key doesn't exist will merge an empty array
        if (!TableRegistry::exists($key)) {
            return [];
        }

        $query = TableRegistry::get($key);
        $data = $query->find('list', ['keyField' => 'key', 'valueField' => 'value'])->toArray();
        $config = Hash::expand($data);

        return $config;
    }

    /**
     * @param string $key Table name
     * @param array $data Data to dump.
     * @return void
     * @throws \Exception
     */
    public function dump($key, array $data)
    {
        throw new Exception('Not implemented');
    }
}
