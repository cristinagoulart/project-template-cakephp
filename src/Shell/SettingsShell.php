<?php
namespace App\Shell;

use App\Model\Entity\Setting;
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
     * @return null Success or error code.
     */
    public function main()
    {
        $this->out('Populating the DB');

        $settings = Configure::read('Settings');
        $query = TableRegistry::get('Settings');
        foreach ($settings as $field => $data) {
            $unit = $query->newEntity([
                'key' => $data['alias'],
                'value' => Configure::read($data['alias']),
            ]);

            try {
                $query->save($unit);
                $this->out(sprintf("|%5.5s |%-40.40s | %-40.40s |", $unit->id, $unit->key, $unit->value));
            } catch (\Exception $e) {
                if ($e->errorInfo[1] == 1062) {
                    $this->out(sprintf("|%5.5s |%-40.40s | %-40.40s |", 'EXIST', $unit->key, $unit->value));
                } else {
                    $this->out($e);
                }
            }
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
