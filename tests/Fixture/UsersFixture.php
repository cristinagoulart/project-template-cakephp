<?php
namespace App\Test\Fixture;

use CakeDC\Users\Test\Fixture\UsersFixture as BaseFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends BaseFixture
{
    public $import = ['model' => 'Users'];

    /**
     * Add the trashed field
     */
    public function init()
    {
        $this->fields['trashed'] = ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null];
        foreach ($this->records as $key => $record) {
            $this->records[$key]['trashed'] = null;
        }

        parent::init();
    }
}
