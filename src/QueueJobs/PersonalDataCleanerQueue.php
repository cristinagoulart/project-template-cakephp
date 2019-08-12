<?php
namespace App\QueueJobs;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use josegonzalez\Queuesadilla\Job\Base;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

class PersonalDataCleanerQueue
{
    /**
     * [addRecord description]
     * @param object $job [description]
     * @return void
     */
    public static function addRecord(Object $job) : void
    {
        Assert::isInstanceOf($job, Base::class);
        $data = $job->data();

        if (empty($data['module']) || empty($data['record_id'])) {
            return;
        }

        $config = (array)(new ModuleConfig(ConfigType::FIELDS(), $data['module']))->parseToArray();
        $fields = array_filter($config, function ($v) {
            return in_array('personal', (array)array_keys($v));
        });

        $emptyData = array_map(function () {
            return '';
        }, $fields);

        $table = TableRegistry::getTableLocator()->get($data['module']);
        $toUpdate = $table->find()->where(['id' => $data['record_id']])->first();
        Assert::isInstanceOf($toUpdate, EntityInterface::class);
        $toUpdate = $table->patchEntity($toUpdate, $emptyData, ['validate' => false]);

        if (!$table->save($toUpdate)) {
            // throw new \Exception($toUpdate->getErrors());
        }
    }
}
