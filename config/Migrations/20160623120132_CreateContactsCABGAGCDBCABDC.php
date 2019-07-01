<?php
use CsvMigrations\CsvMigration;

class CreateContactsCABGAGCDBCABDC extends CsvMigration
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
        if (!$this->hasTable('contacts')) {
            $table = $this->table('contacts');
            $table = $this->csv($table);
            $table->create();
        }

        $joinedTables = $this->joins('contacts');
        if (!empty($joinedTables)) {
            foreach ($joinedTables as $joinedTable) {
                $joinedTable->create();
            }
        }
    }
}
