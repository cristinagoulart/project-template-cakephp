<?php
use Migrations\AbstractMigration;

class CreateAdminSetting extends AbstractMigration
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
        $table = $this->table('admin_settings');
        $table->addColumn('key', 'string', [
            'default' => null,
            'null' => false,
            'unique' => true,
        ]);
        $table->addColumn('value', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addPrimaryKey([
            'id',
        ]);
        $table->create();
    }
}
