<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Shell\Task;

use Bake\Shell\Task\BakeTask;
use Cake\Console\Shell;
use Cake\I18n\Time;

/**
 * Bake Upgrade<timestamp>Task files used by Upgrade shell.
 *
 */
class UpgradeFileTask extends BakeTask
{
    /**
     * Tasks to be loaded
     *
     * @var array
     */
    public $tasks = [
        'Bake.BakeTemplate',
    ];

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription('Generate UpgradeTask template with timestamp');

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        parent::main();

        $timestamp = Time::now();
        // @NOTE: adding HH:mm:ss to timestamp to avoid overwriting
        // multiple upgrade files in the same date
        $timestamp = $timestamp->i18nFormat('yyyyMMddHHmmss');

        $filename = 'Upgrade' . $timestamp . 'Task.php';
        $data = [
            'timestamp' => $timestamp,
        ];

        $this->BakeTemplate->set($data);
        $contents = $this->BakeTemplate->generate('Shell/Task/upgrade_task');
        $path = $this->getPath() . 'Shell' . DS . 'Task' . DS;

        return $this->createFile($path . $filename, $contents);
    }
}
