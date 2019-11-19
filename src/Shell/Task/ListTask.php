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

use Cake\Console\Shell;
use Cake\Core\Plugin;

/**
 * Task for listing loaded plugins.
 *
 */
class ListTask extends Shell
{
    /**
     * Execution method always used for tasks.
     *
     * @return bool
     */
    public function main()
    {
        $plugins = $this->loadedPlugins();

        if (empty($plugins)) {
            $this->err('<error>No loaded plugins detected.</error>');

            return false;
        }

        $this->out('Loaded Plugins: ' . implode(', ', $plugins));
        $this->hr();

        return true;
    }

    /**
     * Method that retrieves and returns an array with all loaded plugins.
     * @return string[]
     */
    public function loadedPlugins(): array
    {
        /**
         * @var array $result
         */
        $result = Plugin::loaded();

        return $result;
    }
}
