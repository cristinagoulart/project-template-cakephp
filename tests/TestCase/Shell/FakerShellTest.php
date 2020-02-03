<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class FakerShellTest extends ConsoleIntegrationTestCase
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
        $this->table->deleteAll([]);

        EventManager::instance()->on('Model.beforeMarshal', function (Event $event, \ArrayObject $data, \ArrayObject $options) {
            unset($data['id']);

            $data['country'] = 'CY';
            $data['currency'] = 'EUR';
            $data['email'] = uniqid() . '@company.com';
            $data['gender'] = 'm';
        });

        $this->exec('faker ' . $this->table->getAlias(), ['1', 11, 6]);

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<success>10 fake records have been created successfully.</success>');
        $this->assertCount(10, $this->table->find());
    }

    public function testMainWithoutSelection(): void
    {
        $this->exec('faker ' . $this->table->getAlias());

        $this->assertExitCode(Shell::CODE_ERROR);
        $this->assertErrorContains('<error>Aborting, no columns selected.</error>');
    }

    public function testMainWithInvalidSelection(): void
    {
        $this->exec('faker ' . $this->table->getAlias(), [PHP_INT_MAX]);

        $this->assertExitCode(Shell::CODE_ERROR);
        $this->assertErrorContains('<error>Aborting, no columns selected.</error>');
    }

    public function testMainWithoutModel(): void
    {
        $this->table->deleteAll([]);

        $this->exec('faker');

        $this->assertExitCode(Shell::CODE_ERROR);
    }
}
