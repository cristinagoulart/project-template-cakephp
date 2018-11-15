<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Settings shell command.
 */

class SettingsShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('reset', [
                'help' => 'Delete one key from the database',
                'parser' => [
                    'options' => [
                        'reset' => [
                            'help' => __('Insert key'),
                            'required' => true,
                        ]
                    ]
                ],
            ]);
        $parser->addSubcommand('resetAll', [
               'help' => 'Drop table Settings',
                'parser' => [
                    'options' => [
                        'reset' => [
                            'required' => false,
                        ]
                    ]
                ],
            ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return null
     * @throws RuntimeException
     */
    public function main()
    {
        $query = TableRegistry::get('Settings');
        $alias = Hash::combine(Configure::read('Settings'), '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.type');
        $links = Hash::filter(Hash::combine(Configure::read('Settings'), '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.links'));

        $settings = $query->getAliasDiff(array_keys($alias));

        $data = [];
        foreach ($settings as $set) {
            $data[] = $this->setData($alias, $set, $set);
            if (empty($links[$set])) {
                continue;
            }
            foreach ($links[$set] as $aliases => $value) {
                if (in_array($value, $settings)) {
                    throw new \Exception('Duble alias found');
                }
                $data[] = $this->setData($alias, $set, $value);
            }
        }

        try {
            $entities = $query->newEntities($data);
            if ($query->saveMany($entities)) {
                $this->out('Settings successfully updated');
            } else {
                $this->out('Failed to update settings, please try again.');
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return null;
    }

    /**
     * Prepare array for new entity
     * @param string $alias alias
     * @param string $index index
     * @param string $value value
     * @return array
     */
    private function setData($alias, $index, $value)
    {
        return [
            'key' => $value,
            'value' => Configure::read($index),
            'scope' => 'app',
            'context' => 'app',
            'type' => $alias[$index] // dynamic field to pass `type` to the validator
        ];
    }

    /**
     * reset() method. Truncate key in table Settings
     * @param string $key key of DB to delete
     * @return void
     */
    public function reset($key = '')
    {
        $query = TableRegistry::get('Settings');
        if (empty($key)) {
            $this->out('Insert key to delete');

            return;
        }

        $query->deleteAll(['key' => $key]);
    }

    /**
     * resetAll() method. Truncate all table Settings
     * @return void
     */
    public function resetAll()
    {
        $query = TableRegistry::get('Settings');
        $this->out('Truncate table Settings');
        $query->deleteAll([]);
    }
}
