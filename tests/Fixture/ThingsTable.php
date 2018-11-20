<?php

namespace App\Test\Fixture;

use CsvMigrations\Table;

class ThingsTable extends Table
{
    public function getFieldsDefinitions(array $stubFields = [])
    {
        return [
            'searchable' => [
                'name' => 'searchable',
                'type' => 'string',
                'required' => null,
                'non-searchable' => null,
                'unique' => null,
            ],
            'non-searchable' => [
                'name' => 'non-searchable',
                'type' => 'string',
                'required' => null,
                'non-searchable' => 1,
                'unique' => null,
            ]
        ];
    }
}
