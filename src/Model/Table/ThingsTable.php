<?php

namespace App\Model\Table;

/**
 * Things Model
 *
 */
class ThingsTable extends AppTable
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

        $this->setTable('things');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}
