<?php

namespace App\Test\Fixture;

use CsvMigrations\Table;

class ThingsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Users')->setForeignKey('created_by');
    }

    public function getFieldsDefinitions(array $stubFields = []): array
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
            ],
            'trashed' => [
                'name' => 'trashed',
                'type' => 'datetime',
                'required' => null,
                'non-searchable' => null,
                'unique' => null,
            ]
        ];
    }
}
