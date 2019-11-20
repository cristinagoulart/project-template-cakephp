<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class DatetimeFix extends AbstractMigration
{

    public $autoId = false;

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('datetime_fix');

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('module', 'string', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('record_id', 'string', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('updated', 'boolean', [
            'default' => false,
            'null' => false,
        ]);

        $table->addPrimaryKey(['id']);

        $table->create();
    }
}
