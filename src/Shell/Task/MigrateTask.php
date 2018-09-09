<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Shell\Task;

use App\Shell\Task\ListTask;
use Cake\Console\Shell;

/**
 * Task for migrating plugins.
 *
 */
class MigrateTask extends Shell
{
    /**
     * Execution method always used for tasks.
     *
     * @return bool
     */
    public function main()
    {
        $listTask = new ListTask();
        $plugins = $listTask->loadedPlugins();

        if (empty($plugins)) {
            $this->err('<error>No loaded plugins detected.</error>');

            return false;
        }

        $this->out('Migrating all loaded Plugins:');
        $this->hr();
        $this->_migratePlugins($plugins);

        return true;
    }

    /**
     * Method that runs migrations of all loaded plugins.
     * @param  array $plugins loaded plugins
     * @return void
     */
    protected function _migratePlugins(array $plugins)
    {
        $dispatchCommand = 'migrations migrate';

        // datasource connection
        if (!empty($this->params['connection'])) {
            $dispatchCommand .= ' -c ' . $this->params['connection'];
        }

        foreach ($plugins as $plugin) {
            $this->out('Migrating plugin [' . $plugin . ']:');
            $this->dispatchShell([
               'command' => $dispatchCommand . ' --plugin ' . $plugin
            ]);
            $this->hr();
        }
    }
}
