<?php

namespace App\Test\TestCase\Utility;

use App\Utility\Model;
use Cake\TestSuite\TestCase;

class ModelTest extends TestCase
{
    public $fixtures = ['app.things'];

    public function testFields(): void
    {
        $fields = Model::fields('Things');

        // 32 migration.json fields, +4 from combined fields, +1 trashed field
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

    public function testAssociations(): void
    {
        $expected = [
            [
                'name' => 'AssignedToUsers',
                'label' => 'Users (Assigned To)',
                'model' => 'Users',
                'type' => 'manyToOne',
                'primary_key' => 'id',
                'foreign_key' => 'assigned_to'
            ],
            [
                'name' => 'CreatedByUsers',
                'label' => 'Users (Created By)',
                'model' => 'Users',
                'type' => 'manyToOne',
                'primary_key' => 'id',
                'foreign_key' => 'created_by'
            ],
            [
                'name' => 'FileFileStorageFileStorage',
                'label' => 'Burzum/FileStorage.FileStorage (Foreign Key)',
                'model' => 'Burzum/FileStorage.FileStorage',
                'type' => 'oneToMany',
                'primary_key' => 'id',
                'foreign_key' => 'foreign_key'
            ],
            [
                'name' => 'ModifiedByUsers',
                'label' => 'Users (Modified By)',
                'model' => 'Users',
                'type' => 'manyToOne',
                'primary_key' => 'id',
                'foreign_key' => 'modified_by'
            ],
            [
                'name' => 'PhotosFileStorageFileStorage',
                'label' => 'Burzum/FileStorage.FileStorage (Foreign Key)',
                'model' => 'Burzum/FileStorage.FileStorage',
                'type' => 'oneToMany',
                'primary_key' => 'id',
                'foreign_key' => 'foreign_key'
            ],
            [
                'name' => 'PrimaryThingThings',
                'label' => 'Things (Primary Thing)',
                'model' => 'Things',
                'type' => 'manyToOne',
                'primary_key' => 'id',
                'foreign_key' => 'primary_thing'
            ],
            [
                'name' => 'Thingsprimary_thing',
                'label' => 'Things (Primary Thing)',
                'model' => 'Things',
                'type' => 'oneToMany',
                'primary_key' => 'id',
                'foreign_key' => 'primary_thing'
            ]
        ];

        $associations = Model::associations('Things');

        usort($associations, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $this->assertSame($expected, $associations);
    }
}
