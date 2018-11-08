<?php
namespace App\Settings;

use Cake\Core\Configure\ConfigEngineInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class DbConfig implements ConfigEngineInterface
{
    /**
     * @param string $key Table name with the settings
     * @return array
     * @throws Exception
     */
    public function read($key)
    {
        $query = TableRegistry::get($key);

        // if the table $key doesn't exist will merge an empty array
        try {
            $data = $query->find('list', ['keyField' => 'key', 'valueField' => 'value'])->toArray();
        } catch (\Exception $e) {
            return [];
        }
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
