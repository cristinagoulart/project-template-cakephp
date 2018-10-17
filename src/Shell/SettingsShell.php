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
                'help' => 'Reset configuartion',
                'parser' => [
                    'options' => [
                        'reset' => [
                            'help' => __('Truncate table Settings'),
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
        $settings = $query->getAliasDiff(array_keys($alias));

        $data = [];
        foreach ($settings as $set) {
            $data[] = [
                    'key' => $set,
                    'value' => Configure::read($set),
                    'type' => $alias[$set] // dynamic field to pass `type` to the validator
                ];
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
     * reset() method. Truncate table Settings
     *
     * @return void
     */
    public function reset()
    {
        $this->out('Truncate table Settings');
        $query = TableRegistry::get('Settings');
        $query->deleteAll([]);
    }
}
