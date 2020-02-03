<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class PluginShellTest extends ConsoleIntegrationTestCase
{
    public function testList(): void
    {
        $this->exec('plugin list');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('Loaded Plugins: ');
        $this->assertOutputContains(' ADmad/JwtAuth');
        $this->assertOutputContains(' AdminLTE');
        $this->assertOutputContains(' Alt3/Swagger');
        $this->assertOutputContains(' AuditStash');
        $this->assertOutputContains(' Burzum/FileStorage');
        $this->assertOutputContains(' CakeDC/Users');
        $this->assertOutputContains(' Crud');
        $this->assertOutputContains(' CsvMigrations');
        $this->assertOutputContains(' Groups');
        $this->assertOutputContains(' Menu');
        $this->assertOutputContains(' Migrations');
        $this->assertOutputContains(' Qobo/Utils');
        $this->assertOutputContains(' RolesCapabilities');
        $this->assertOutputContains(' Search');
        $this->assertOutputContains(' Translation');
    }
}
