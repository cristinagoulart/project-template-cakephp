<?php
namespace App\Model\Table;

use DatabaseLog\Model\Table\DatabaseLogsTable;

class LogsTable extends DatabaseLogsTable
{
    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('database_logs');
        $this->addBehavior('Search.Searchable');
    }
}
