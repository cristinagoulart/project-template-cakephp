<?php
use CsvMigrations\CsvMigration;

class Things20190712073250 extends CsvMigration
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
        $table = $this->table('things');
        $table = $this->csv($table);

        if (!$this->hasTable('things')) {
            $table->create();
        } else {
            $table->update();
        }
    }
}
