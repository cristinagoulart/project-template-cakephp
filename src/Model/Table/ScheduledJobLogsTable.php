<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ScheduledJobLogs Model
 *
 * @property \App\Model\Table\ScheduledJobsTable|\Cake\ORM\Association\BelongsTo $ScheduledJobs
 *
 * @method \App\Model\Entity\ScheduledJobLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\ScheduledJobLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ScheduledJobLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ScheduledJobLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ScheduledJobLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ScheduledJobLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ScheduledJobLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScheduledJobLogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('scheduled_job_logs');
        $this->setDisplayField('context');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ScheduledJobs', [
            'foreignKey' => 'scheduled_job_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('context')
            ->maxLength('context', 255)
            ->allowEmpty('context');

        $validator
            ->scalar('status')
            ->maxLength('status', 255)
            ->allowEmpty('status');

        $validator
            ->dateTime('datetime')
            ->requirePresence('datetime', 'create')
            ->notEmpty('datetime');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['scheduled_job_id'], 'ScheduledJobs'));

        return $rules;
    }

    /**
     * Log Scheduled Job script
     *
     * @param \Cake\Datasource\EntityInterface $entity of the scheduled job
     * @param mixed[] $state of the script response
     * @param \Cake\I18n\Time $stamp of currently executed cron iteration
     *
     * @return mixed $result containing bool or inserted Id
     */
    public function logJob(EntityInterface $entity, array $state, Time $stamp)
    {
        $logEntity = $this->newEntity();

        $logEntity->scheduled_job_id = $entity->id;
        $logEntity->context = $entity->job;
        $logEntity->status = $state['state'];
        $logEntity->datetime = $stamp->i18nFormat('yyyy-mm-dd HH:mm:00');
        $logEntity->extra = json_encode($state);
        $logEntity->created = Time::now();

        $saved = $this->save($logEntity);

        return $saved;
    }
}
