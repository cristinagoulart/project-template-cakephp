<?php
use Migrations\AbstractMigration;

class ScheduledPersonalData extends AbstractMigration
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
        $table = $this->table('scheduled_personal_data', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('module', 'string', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('record_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('scheduled', 'datetime', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('status', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('errors', 'string', [
            'default' => null,
            'null' => true,
        ]);

        $table->addPrimaryKey([
            'id',
        ]);

        $table->create();
    }
}
