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

/**
 * Task for migrating plugins.
 * @todo add support for CakePHP Migrations rollback, status and mark_migrated commands
 *
 * @property \Migrations\Shell\Task\MigrateTask $Migrate
 */
class MigrationsTask extends Shell
{
    /**
     * Tasks to load
     *
     * @var array
     */
    public $tasks = [
        'Migrate'
    ];

    /**
     * Method that loads plugins migrate task
     * @return void
     */
    public function migrate(): void
    {
        $this->Migrate->main();
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->addSubcommand('migrate', ['help' => 'Migrate all loaded plugins'])
            ->addOption('connection', [
                'short' => 'c',
                'help' => 'The datasource connection to use',
                'required' => false
            ]);

        return $parser;
    }
}
