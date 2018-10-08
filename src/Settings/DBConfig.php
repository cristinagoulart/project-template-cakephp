<?php
namespace App\Settings;

use Cake\Core\Configure\ConfigEngineInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class DBConfig implements ConfigEngineInterface
{
    /**
     * @param string $key is table name with the settings
     * @return array
     * @throws \Exception
     */
    public function read($key)
    {
        $query = TableRegistry::get($key);

        try {
            $data = $query->find('list', ['keyField' => 'key', 'valueField' => 'value'])->toArray();
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Table "%s" did not return an array', $key));
        }
        $config = Hash::expand($data);

        return $config;
    }

    /**
     * Not implemented yet.
     */
    public function dump($key, array $data)
    {
        return;
    }
}
