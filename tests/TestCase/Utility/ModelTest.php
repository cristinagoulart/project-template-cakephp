<?php
namespace App\Test\TestCase\Utility;

use App\Utility\Model;
use Cake\TestSuite\TestCase;

class ModelTest extends TestCase
{
    public $fixtures = ['app.things'];

    public function testFields() : void
    {
        $fields = Model::fields('Things');

        // 37 migration.json fields, +4 from combined fields, +1 trashed field
        $this->assertCount(37, $fields);

        foreach ($fields as $field) {
            $this->assertArrayHasKey('name', $field);
            $this->assertInternalType('string', $field['name']);

            $this->assertArrayHasKey('label', $field);
            $this->assertInternalType('string', $field['label']);

            $this->assertArrayHasKey('type', $field);
            $this->assertInternalType('string', $field['type']);

            $this->assertArrayHasKey('db_type', $field);
            $this->assertInternalType('string', $field['db_type']);

            $this->assertArrayHasKey('meta', $field);
            $this->assertInternalType('array', $field['meta']);
        }
    }
}
