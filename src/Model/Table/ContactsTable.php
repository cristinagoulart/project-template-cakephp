<?php
namespace App\Model\Table;

/**
 * Contacts Model
 *
 */
class ContactsTable extends AppTable
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

        $this->setTable('contacts');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}
