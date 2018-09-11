<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Log\Log;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;

class AuditStashShell extends Shell
{
    /**
     *  LogAudit table instance.
     *
     * @var \App\Model\Table\LogAuditTable
     */
    private $table;

    /**
     * Pagination limit.
     *
     * @var integer
     */
    private $limit = 100;

    /**
     * Shell specific where clause.
     *
     * @var array
     */
    private $where = ['user_id IS NULL', 'meta LIKE' => '%"user":%'];

    /**
     * Deletes log records older than specified time (maxLength).
     *
     * @return void
     */
    public function addUserId()
    {
        $this->info('Populating Log Audit "user_id" column, this might take a while.');

        $this->table = TableRegistry::get('LogAudit');

        $count = $this->table->find()
            ->where($this->where)
            ->count();

        if (0 === $count) {
            $this->abort('"user_id" is already populated for all Log Audit records.');
        }

        $pages = $count / $this->limit;
        for ($i = 0; $i < $pages; $i++) {
            $this->updateRecords($this->fetchRecords($i));
        }

        $this->success('Log Audit "user_id" column has been populated.');
    }

    /**
     * Fetch log audit records with null "user_id" value.
     *
     * @param int $offset Pagination offset
     * @return \Cake\ORM\ResultSet
     */
    private function fetchRecords($offset)
    {
        return $this->table->find()
            ->select([$this->table->getPrimaryKey(), 'meta'])
            ->where($this->where)
            ->limit($this->limit)
            ->offset($this->limit * $offset)
            ->all();
    }

    /**
     * Update records "user_id" column.
     *
     * @param \Cake\ORM\ResultSet $entities Entities list
     * @return void
     */
    private function updateRecords(ResultSet $entities)
    {
        foreach ($entities as $entity) {
            $entity->set('user_id', json_decode($entity->get('meta'))->user);
        }

        if (! $this->table->saveMany($entities)) {
            Log::warning('Failed to update "user_id" on log_audit');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('add_user_id', [
            'help' => 'Populates user_id column from meta column data.',
        ]);

        return $parser;
    }
}
