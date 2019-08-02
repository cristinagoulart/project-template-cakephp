<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use Webmozart\Assert\Assert;

/**
 * ClearPersonalData shell command.
 */
class ClearPersonalDataShell extends Shell
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
        $parser->setDescription('Clean Private data from all or seleced Module');

        $parser->addOption('modules', [
            'short' => 'm',
            'help' => 'Module Names separated with "," eg. "Accounts,Contacts"',
            'default' => ''
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|void Success or error code.
     */
    public function main()
    {
        $list = [];
        if (!empty($this->param('modules'))) {
            $params = array_map('trim', explode(',', (string)$this->param('modules')));
            $modelsList = Utility::getModels();

            $list = array_intersect($params, $modelsList);

            if (empty($list)) {
                $this->abort("No valid modules are listed");
            }

            $notListed = array_diff($params, $list);
            if (!empty($notListed)) {
                $this->info(implode(',', $notListed) . ": not valid modules");
            }
        }

        $scheduledTable = TableRegistry::getTableLocator()->get('ScheduledPersonalData');

        $where['status'] = 'pending';
        empty($list) ?: $where['module IN'] = $list;

        $entities = $scheduledTable->find()->where($where)->all();

        foreach ($entities as $entity) {
            $data = $this->getPersonalField($entity->get('module'));
            $table = TableRegistry::getTableLocator()->get($entity->get('module'));
            $toUpdate = $table->find()->where(['id' => $entity->get('record_id')])->first();
            Assert::isInstanceOf($toUpdate, EntityInterface::class);
            $toUpdate = $table->patchEntity($toUpdate, $data, ['validate' => false]);
            if ($table->save($toUpdate)) {
                $entity = $scheduledTable->patchEntity($entity, ['status' => 'completed']);
                $scheduledTable->save($entity);

                continue;
            }

            $entity = $scheduledTable->patchEntity($entity, ['status' => 'failed']);
            $scheduledTable->save($entity);
        }

        return true;
    }

    /**
     * Provide an array with all the marked personal field of the module.
     *
     * @param  string $moduleName Module name
     * @return mixed[]
     */
    private function getPersonalField(string $moduleName) : array
    {
        $config = (array)(new ModuleConfig(ConfigType::FIELDS(), $moduleName))->parseToArray();
        $fields = array_filter($config, function ($v) {
            return in_array('personal', (array)array_keys($v));
        });

        return array_map(function () {
            return '';
        }, $fields);
    }
}
