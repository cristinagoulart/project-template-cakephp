<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LogAudit Model
 *
 */
class LogAuditTable extends Table
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

        $this->setTable('log_audit');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->dateTime('timestamp')
            ->requirePresence('timestamp', 'create')
            ->notEmpty('timestamp');

        $validator
            ->uuid('primary_key')
            ->requirePresence('primary_key', 'create')
            ->notEmpty('primary_key');

        $validator
            ->requirePresence('source', 'create')
            ->notEmpty('source');

        $validator
            ->allowEmpty('parent_source');

        $validator
            ->allowEmpty('changed');

        $validator
            ->allowEmpty('meta');

        return $validator;
    }
}
