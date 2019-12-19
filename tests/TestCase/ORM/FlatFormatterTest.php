<?php

namespace App\Test\TestCase\ORM;

use App\ORM\FlatFormatter;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Webmozart\Assert\Assert;

class FlatFormatterTest extends TestCase
{
    public $fixtures = [
        'app.Things',
        'app.Users',
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

    public function testFormatResults(): void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new FlatFormatter());

        $result = [];
        foreach (array_keys($query->first()->toArray()) as $item) {
            Assert::string($item);
            $index = substr($item, 0, (int)strpos($item, '.'));
            $result[$index] = '';
        }

        $this->assertSame(['Things' => ''], $result);
    }

    public function testFormatResultsWithContain(): void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->contain('AssignedToUsers')
            ->formatResults(new FlatFormatter());

        $result = [];
        foreach (array_keys($query->first()->toArray()) as $item) {
            Assert::string($item);
            $index = substr($item, 0, (int)strpos($item, '.'));
            $result[$index] = '';
        }

        ksort($result);

        $this->assertSame(['AssignedToUsers' => '', 'Things' => ''], $result);
    }

    public function testFormatResultsWithMatching(): void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->matching('AssignedToUsers')
            ->formatResults(new FlatFormatter());

        $result = [];
        foreach (array_keys($query->first()->toArray()) as $item) {
            Assert::string($item);
            $index = substr($item, 0, (int)strpos($item, '.'));
            $result[$index] = '';
        }

        ksort($result);

        $this->assertSame(['AssignedToUsers' => '', 'Things' => ''], $result);
    }

    public function testFormatResultsWithPermissions(): void
    {
        $query = $this->table
            ->find()
            ->where(['Things.id' => '00000000-0000-0000-0000-000000000001'])
            ->formatResults(new \App\ORM\PermissionsFormatter())
            ->formatResults(new FlatFormatter());

        $keys = array_keys($query->first()->toArray());
        $this->assertTrue(in_array('_permissions', $keys, true));
    }
}
