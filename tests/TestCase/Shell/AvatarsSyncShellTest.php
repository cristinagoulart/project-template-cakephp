<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class AvatarsSyncShellTest extends ConsoleIntegrationTestCase
{
    public $fixtures = ['app.users'];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Users');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testMain(): void
    {
        $this->exec('avatars_sync');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('Avatar sync. Updated: 0. Generated: 10. Users: 10');
    }

    public function testMainWithoutUsers(): void
    {
        $this->table->deleteAll([]);

        $this->exec('avatars_sync');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('No users found for avatar sync. Exiting...');
    }
}
