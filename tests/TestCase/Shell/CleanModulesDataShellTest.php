<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class CleanModulesDataShellTest extends ConsoleIntegrationTestCase
{
    public $fixtures = [
        'app.log_audit',
        'app.things',
    ];

    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Things');
    }

    public function tearDown(): void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testMain(): void
    {
        $thing = $this->table->newEntity([
            'country' => 'CY',
            'currency' => 'EUR',
            'gender' => 'm',
            'email' => uniqid() . '@bar.com',
        ]);

        $this->table->saveOrFail($thing);
        $this->assertTrue(0 < $this->table->find()->count());

        $this->exec('clean_modules_data --modules=' . $this->table->getAlias());
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertSame(0, $this->table->find()->count());
    }

    public function testMainWithoutModules(): void
    {
        $thing = $this->table->newEntity([
            'country' => 'CY',
            'currency' => 'EUR',
            'gender' => 'm',
            'email' => uniqid() . '@bar.com',
        ]);

        $this->table->saveOrFail($thing);

        $initialCount = $this->table->find()->count();

        $this->exec('clean_modules_data');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertSame($initialCount, $this->table->find()->count());
    }
}
