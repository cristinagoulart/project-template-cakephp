<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * AdminSetting shell command.
 */
class AdminSettingShell extends Shell
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

        return $parser;
    }

    /**
     * main() method.
     *
     * @return null Success or error code.
     */
    public function main()
    {
        $this->out('Populating the BD');

        $settings = Configure::read('AdminSetting');

        $query = TableRegistry::get('AdminSettings');
        $query->deleteAll([]);

        foreach ($settings as $field => $data) {
            $unit = $query->newEntity();
            $unit->key = $data['alias'];
            $unit->value = Configure::read($data['alias']);

            if ($query->save($unit)) {
                $this->out($unit->id);
            }
        }

        return null;
    }
}
