<?php

namespace App\Test\TestCase\Utility;

use App\Utility\Field;
use Cake\TestSuite\TestCase;

class FieldTest extends TestCase
{
    public $fixtures = ['app.things', 'app.users'];

    public function testName(): void
    {
        $this->assertSame('id', (new Field('Things', 'id'))->name());
        $this->assertSame('gender', (new Field('Things', 'gender'))->name());
        $this->assertSame('name', (new Field('Things', 'name'))->name());

        $this->assertSame('id', (new Field('Users', 'id'))->name());
        $this->assertSame('username', (new Field('Users', 'username'))->name());
        $this->assertSame('email', (new Field('Users', 'email'))->name());
    }

    public function testLabel(): void
    {
        $this->assertSame('Id', (new Field('Things', 'id'))->label());
        $this->assertSame('Gender', (new Field('Things', 'gender'))->label());
        // with custom label
        $this->assertSame('label name', (new Field('Things', 'name'))->label());

        $this->assertSame('Id', (new Field('Users', 'id'))->label());
        $this->assertSame('Username', (new Field('Users', 'username'))->label());
        $this->assertSame('Email', (new Field('Users', 'email'))->label());
    }

    public function testType(): void
    {
        $this->assertSame('uuid', (new Field('Things', 'id'))->type());
        $this->assertSame('list', (new Field('Things', 'gender'))->type());
        $this->assertSame('string', (new Field('Things', 'name'))->type());
        $this->assertSame('decimal', (new Field('Things', 'area_amount'))->type());
        $this->assertSame('list', (new Field('Things', 'salary_currency'))->type());
        $this->assertSame('list', (new Field('Things', 'area_unit'))->type());
        $this->assertSame('datetime', (new Field('Things', 'trashed'))->type());
        $this->assertSame('country', (new Field('Things', 'country'))->type());
        $this->assertSame('currency', (new Field('Things', 'currency'))->type());

        $this->assertSame('uuid', (new Field('Users', 'id'))->type());
        $this->assertSame('string', (new Field('Users', 'username'))->type());
        $this->assertSame('string', (new Field('Users', 'email'))->type());
    }

    public function testDatabaseType(): void
    {
        $this->assertSame('uuid', (new Field('Things', 'id'))->databaseType());
        $this->assertSame('string', (new Field('Things', 'gender'))->databaseType());
        $this->assertSame('uuid', (new Field('Things', 'assigned_to'))->databaseType());

        $this->assertSame('uuid', (new Field('Users', 'id'))->databaseType());
        $this->assertSame('string', (new Field('Users', 'password'))->databaseType());
        $this->assertSame('string', (new Field('Users', 'username'))->databaseType());
    }

    public function testMeta(): void
    {
        $this->assertSame(['required', 'unique'], (new Field('Things', 'id'))->meta());
        $this->assertSame([], (new Field('Things', 'name'))->meta());
        $this->assertSame(['required'], (new Field('Things', 'country'))->meta());
        $this->assertSame(['non-searchable'], (new Field('Things', 'non_searchable'))->meta());
        $this->assertSame(['required', 'unique'], (new Field('Things', 'email'))->meta());

        $this->assertSame(['required', 'unique'], (new Field('Users', 'id'))->meta());
        $this->assertSame([], (new Field('Users', 'password'))->meta());
        $this->assertSame([], (new Field('Users', 'username'))->meta());
    }

    public function testStateWitUuidType(): void
    {
        $expected = [
            'name' => 'id',
            'label' => 'Id',
            'type' => 'uuid',
            'db_type' => 'uuid',
            'meta' => ['required', 'unique'],
        ];

        $this->assertSame($expected, (new Field('Things', 'id'))->state());
    }

    public function testStateWithStringType(): void
    {
        $expected = [
            'name' => 'name',
            'label' => 'label name',
            'type' => 'string',
            'db_type' => 'string',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'name'))->state());
    }

    public function testStateWitTextType(): void
    {
        $expected = [
            'name' => 'description',
            'label' => 'label description',
            'type' => 'text',
            'db_type' => 'text',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'description'))->state());
    }

    public function testStateWithDecimalType(): void
    {
        $expected = [
            'name' => 'rate',
            'label' => 'Rate',
            'type' => 'decimal',
            'db_type' => 'decimal',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'rate'))->state());
    }

    public function testStateWithIntegerType(): void
    {
        $expected = [
            'name' => 'level',
            'label' => 'Level',
            'type' => 'integer',
            'db_type' => 'integer',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'level'))->state());
    }

    public function testStateWithDateType(): void
    {
        $expected = [
            'name' => 'date_of_birth',
            'label' => 'Date Of Birth',
            'type' => 'date',
            'db_type' => 'date',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'date_of_birth'))->state());
    }

    public function testStateWithTimeType(): void
    {
        $expected = [
            'name' => 'work_start',
            'label' => 'Work Start',
            'type' => 'time',
            'db_type' => 'time',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'work_start'))->state());
    }

    public function testStateWithDatetimeType(): void
    {
        $expected = [
            'name' => 'created',
            'label' => 'Created',
            'type' => 'datetime',
            'db_type' => 'datetime',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'created'))->state());
    }

    public function testStateWitBooleanType(): void
    {
        $expected = [
            'name' => 'vip',
            'label' => 'Vip',
            'type' => 'boolean',
            'db_type' => 'boolean',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'vip'))->state());
    }

    public function testStateWitBlobType(): void
    {
        $expected = [
            'name' => 'bio',
            'label' => 'Bio',
            'type' => 'blob',
            'db_type' => 'binary',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'bio'))->state());
    }

    public function testStateWithRelatedType(): void
    {
        $expected = [
            'name' => 'assigned_to',
            'label' => 'Assigned To',
            'type' => 'related',
            'db_type' => 'uuid',
            'meta' => [],
            'source' => 'users',
            'display_field' => 'name',
        ];

        $this->assertSame($expected, (new Field('Things', 'assigned_to'))->state());
    }

    public function testStateWithReminderType(): void
    {
        $expected = [
            'name' => 'appointment',
            'label' => 'Appointment',
            'type' => 'reminder',
            'db_type' => 'datetime',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'appointment'))->state());
    }

    public function testStateWithPhoneType(): void
    {
        $expected = [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'phone',
            'db_type' => 'string',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'phone'))->state());
    }

    public function testStateWithEmailType(): void
    {
        $expected = [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'db_type' => 'string',
            'meta' => ['required', 'unique'],
        ];

        $this->assertSame($expected, (new Field('Things', 'email'))->state());
    }

    public function testStateWithListType(): void
    {
        $expected = [
            'name' => 'title',
            'label' => 'Title',
            'type' => 'list',
            'db_type' => 'string',
            'meta' => [],
            'options' => [
                ['value' => 'Mr', 'label' => 'Mr'],
                ['value' => 'Mrs', 'label' => 'Mrs'],
                ['value' => 'Ms', 'label' => 'Ms'],
                ['value' => 'Dr', 'label' => 'Dr'],
            ],
        ];

        $this->assertSame($expected, (new Field('Things', 'title'))->state());
    }

    public function testStateWithSublistType(): void
    {
        $expected = [
            'name' => 'test_list',
            'label' => 'Test List',
            'type' => 'sublist',
            'db_type' => 'string',
            'meta' => [],
            'options' => [
                ['value' => 'first', 'label' => 'first'],
                ['value' => 'first.first_children', 'label' => ' - first children'],
                ['value' => 'first.second_children', 'label' => ' - second children'],
                ['value' => 'second', 'label' => 'second'],
            ],
        ];

        $this->assertSame($expected, (new Field('Things', 'test_list'))->state());
    }

    public function testStateWithCountryType(): void
    {
        $expected = [
            'name' => 'country',
            'label' => 'Country',
            'type' => 'country',
            'db_type' => 'string',
            'meta' => ['required'],
        ];

        $result = (new Field('Things', 'country'))->state();

        $this->assertCount(253, $result['options']);
        unset($result['options']);

        $this->assertSame($expected, $result);
    }

    public function testStateWithCurrencyType(): void
    {
        $expected = [
            'name' => 'currency',
            'label' => 'Currency',
            'type' => 'currency',
            'db_type' => 'string',
            'meta' => ['required'],
            'options' => [
                ['value' => 'EUR', 'label' => '<span title="Euro">€&nbsp;(EUR)</span>'],
                ['value' => 'GBP', 'label' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>'],
                ['value' => 'USD', 'label' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>'],
            ],
        ];

        $this->assertSame($expected, (new Field('Things', 'currency'))->state());
    }

    public function testStateWithFilesType(): void
    {
        $expected = [
            'name' => 'photos',
            'label' => 'Photos',
            'type' => 'files',
            'db_type' => 'uuid',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'photos'))->state());
    }

    public function testStateWithMetricType(): void
    {
        $expected = [
            'name' => 'testmetric_unit',
            'label' => 'Testmetric Unit',
            'type' => 'list',
            'db_type' => 'string',
            'meta' => [],
            'options' => [
                ['value' => 'm', 'label' => 'm²'],
                ['value' => 'ft', 'label' => 'ft²'],
            ],
        ];

        $this->assertSame($expected, (new Field('Things', 'testmetric_unit'))->state());

        $expected = [
            'name' => 'testmetric_amount',
            'label' => 'Testmetric Amount',
            'type' => 'decimal',
            'db_type' => 'decimal',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'testmetric_amount'))->state());
    }

    public function testStateWithMoneyType(): void
    {
        $expected = [
            'name' => 'testmoney_currency',
            'label' => 'Testmoney Currency',
            'type' => 'list',
            'db_type' => 'string',
            'meta' => [],
            'options' => [
                ['value' => 'EUR', 'label' => '<span title="Euro">€&nbsp;(EUR)</span>'],
                ['value' => 'GBP', 'label' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>'],
                ['value' => 'USD', 'label' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>'],
            ],
        ];

        $this->assertSame($expected, (new Field('Things', 'testmoney_currency'))->state());

        $expected = [
            'name' => 'testmoney_amount',
            'label' => 'Testmoney Amount',
            'type' => 'decimal',
            'db_type' => 'decimal',
            'meta' => [],
        ];

        $this->assertSame($expected, (new Field('Things', 'testmoney_amount'))->state());
    }
}
