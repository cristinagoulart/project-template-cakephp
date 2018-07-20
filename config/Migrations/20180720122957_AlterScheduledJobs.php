<?php
use Migrations\AbstractMigration;

class AlterScheduledJobs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('scheduled_jobs');
        $table->addColumn('last_run_date', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
