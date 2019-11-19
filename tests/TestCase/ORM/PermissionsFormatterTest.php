<?php
namespace App\Test\TestCase\ORM;

use App\ORM\PermissionsFormatter;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Utils\Utility\User;

class PermissionsFormatterTest extends TestCase
{
    public $fixtures = [
        'app.Things',
        'app.Users'
    ];

    private $table;
    private $user;

    public function setUp() : void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Things');
        $this->user = TableRegistry::getTableLocator()
            ->get('Users')
            ->find()
            ->where(['is_superuser' => true])
            ->firstOrFail();
    }

    public function tearDown() : void
    {
        unset($this->user);
        unset($this->table);

        parent::tearDown();
    }

    public function testFormatResults() : void
    {
        $expected = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];

        User::setCurrentUser($this->user->toArray());

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new PermissionsFormatter());

        $this->assertSame($expected, $query->first()->get('_permissions'));
    }

    public function testFormatResultsWithoutPrimaryKey() : void
    {
        User::setCurrentUser($this->user->toArray());

        $query = $this->table
            ->find()
            ->select('name')
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new PermissionsFormatter());

        $this->assertFalse($query->first()->has('_permissions'));
    }

    public function testFormatResultsWithoutCurrentUser() : void
    {
        $expected = [
            'view' => false,
            'edit' => false,
            'delete' => false
        ];

        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new PermissionsFormatter());

        $this->assertSame($expected, $query->first()->get('_permissions'));
    }
}
