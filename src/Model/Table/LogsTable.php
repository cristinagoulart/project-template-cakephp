<?php
namespace App\Model\Table;

use Cake\Validation\Validator;
use DatabaseLog\Model\Table\DatabaseLogsTable;

/**
 * DatabaseLogs Model
 *
 * @method \App\Model\Entity\Log get($primaryKey, $options = [])
 * @method \App\Model\Entity\Log newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Log[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Log|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Log patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Log[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Log findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LogsTable extends DatabaseLogsTable
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

        $this->setTable('database_logs');
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('type')
            ->maxLength('type', 50)
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->scalar('message')
            ->requirePresence('message', 'create')
            ->notEmpty('message');

        $validator
            ->scalar('context')
            ->allowEmpty('context');

        $validator
            ->scalar('ip')
            ->maxLength('ip', 50)
            ->allowEmpty('ip');

        $validator
            ->scalar('hostname')
            ->maxLength('hostname', 50)
            ->allowEmpty('hostname');

        $validator
            ->scalar('uri')
            ->allowEmpty('uri');

        $validator
            ->scalar('refer')
            ->maxLength('refer', 255)
            ->allowEmpty('refer');

        $validator
            ->scalar('user_agent')
            ->maxLength('user_agent', 255)
            ->allowEmpty('user_agent');

        $validator
            ->integer('count')
            ->requirePresence('count', 'create')
            ->notEmpty('count');

        return $validator;
    }
}
