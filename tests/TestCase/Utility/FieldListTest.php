<?php

namespace App\Test\TestCase\Utility;

use App\Utility\FieldList;
use Cake\TestSuite\TestCase;

class FieldListTest extends TestCase
{
    public $fixtures = ['app.things'];

    /**
     * @dataProvider hasProvider
     */
    public function testHas(string $field, bool $expected): void
    {
        $list = new FieldList('Things', $field);

        $expected ? $this->assertTrue($list->has()) : $this->assertFalse($list->has());
    }

    /**
     * @dataProvider nameProvider
     */
    public function testName(string $field, string $expected): void
    {
        $this->assertSame($expected, (new FieldList('Things', $field))->name());
    }

    public function testOptionsWithNonListField(): void
    {
        $this->assertSame([], (new FieldList('Things', 'id'))->options());
    }

    public function testOptions(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first'],
            ['value' => 'first.first_children', 'label' => ' - first children'],
            ['value' => 'first.second_children', 'label' => ' - second children'],
            ['value' => 'second', 'label' => 'second']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options());
    }

    public function testOptionsWithoutFiltering(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first'],
            ['value' => 'first.first_children', 'label' => ' - first children'],
            ['value' => 'first.second_children', 'label' => ' - second children'],
            ['value' => 'second', 'label' => 'second'],
            ['value' => 'third', 'label' => 'third']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options(['filter' => false]));
    }

    public function testOptionsWithoutFilteringAndFlattening(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first', 'children' => [
                ['value' => 'first.first_children', 'label' => ' - first children'],
                ['value' => 'first.second_children', 'label' => ' - second children']
            ]],
            ['value' => 'second', 'label' => 'second'],
            ['value' => 'third', 'label' => 'third']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options([
            'filter' => false,
            'flatten' => false
        ]));
    }

    public function testOptionsWithoutFilteringAndPrettifying(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first'],
            ['value' => 'first.first_children', 'label' => 'first children'],
            ['value' => 'first.second_children', 'label' => 'second children'],
            ['value' => 'second', 'label' => 'second'],
            ['value' => 'third', 'label' => 'third']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options([
            'filter' => false,
            'prettify' => false
        ]));
    }

    public function testOptionsWithoutFlattening(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first', 'children' => [
                ['value' => 'first.first_children', 'label' => ' - first children'],
                ['value' => 'first.second_children', 'label' => ' - second children']
            ]],
            ['value' => 'second', 'label' => 'second']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options(['flatten' => false]));
    }

    public function testOptionsWithoutFlatteningAndPrettifying(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first', 'children' => [
                ['value' => 'first.first_children', 'label' => 'first children'],
                ['value' => 'first.second_children', 'label' => 'second children']
            ]],
            ['value' => 'second', 'label' => 'second']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options([
            'flatten' => false,
            'prettify' => false
        ]));
    }

    public function testOptionsWithoutPrettifying(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first'],
            ['value' => 'first.first_children', 'label' => 'first children'],
            ['value' => 'first.second_children', 'label' => 'second children'],
            ['value' => 'second', 'label' => 'second']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options(['prettify' => false]));
    }

    public function testOptionsWithoutFlags(): void
    {
        $expected = [
            ['value' => 'first', 'label' => 'first', 'children' => [
                ['value' => 'first.first_children', 'label' => 'first children'],
                ['value' => 'first.second_children', 'label' => 'second children']
            ]],
            ['value' => 'second', 'label' => 'second'],
            ['value' => 'third', 'label' => 'third']
        ];

        $this->assertSame($expected, (new FieldList('Things', 'test_list'))->options([
            'filter' => false,
            'flatten' => false,
            'prettify' => false
        ]));
    }

    public function testOptionsWithCountries(): void
    {
        $expected = [
            ['value' => 'AC', 'label' => '<span class="flag-icon flag-icon-ac flag-icon-default"></span>&nbsp;&nbsp;Ascension Island'],
            ['value' => 'AD', 'label' => '<span class="flag-icon flag-icon-ad flag-icon-default"></span>&nbsp;&nbsp;Andorra'],
            ['value' => 'AE', 'label' => '<span class="flag-icon flag-icon-ae flag-icon-default"></span>&nbsp;&nbsp;United Arab Emirates'],
            ['value' => 'AF', 'label' => '<span class="flag-icon flag-icon-af flag-icon-default"></span>&nbsp;&nbsp;Afghanistan'],
            ['value' => 'AG', 'label' => '<span class="flag-icon flag-icon-ag flag-icon-default"></span>&nbsp;&nbsp;Antigua & Barbuda'],
            ['value' => 'AI', 'label' => '<span class="flag-icon flag-icon-ai flag-icon-default"></span>&nbsp;&nbsp;Anguilla'],
            ['value' => 'AL', 'label' => '<span class="flag-icon flag-icon-al flag-icon-default"></span>&nbsp;&nbsp;Albania'],
            ['value' => 'AM', 'label' => '<span class="flag-icon flag-icon-am flag-icon-default"></span>&nbsp;&nbsp;Armenia'],
            ['value' => 'AO', 'label' => '<span class="flag-icon flag-icon-ao flag-icon-default"></span>&nbsp;&nbsp;Angola'],
            ['value' => 'AQ', 'label' => '<span class="flag-icon flag-icon-aq flag-icon-default"></span>&nbsp;&nbsp;Antarctica'],
            ['value' => 'AR', 'label' => '<span class="flag-icon flag-icon-ar flag-icon-default"></span>&nbsp;&nbsp;Argentina'],
            ['value' => 'AS', 'label' => '<span class="flag-icon flag-icon-as flag-icon-default"></span>&nbsp;&nbsp;American Samoa'],
            ['value' => 'AT', 'label' => '<span class="flag-icon flag-icon-at flag-icon-default"></span>&nbsp;&nbsp;Austria'],
            ['value' => 'AU', 'label' => '<span class="flag-icon flag-icon-au flag-icon-default"></span>&nbsp;&nbsp;Australia'],
            ['value' => 'AW', 'label' => '<span class="flag-icon flag-icon-aw flag-icon-default"></span>&nbsp;&nbsp;Aruba']
        ];

        $result = (new FieldList('Things', 'country'))->options();
        usort($result, function (array $a, array $b) {
            return strcmp($a['value'], $b['value']);
        });

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    public function testOptionsWithCurrencies(): void
    {
        $expected = [
            ['value' => 'EUR', 'label' => '<span title="Euro">€&nbsp;(EUR)</span>'],
            ['value' => 'GBP', 'label' => '<span title="United Kingdom Pound">£&nbsp;(GBP)</span>'],
            ['value' => 'USD', 'label' => '<span title="United States Dollar">&#36;&nbsp;(USD)</span>']
        ];

        $result = (new FieldList('Things', 'salary_currency'))->options();
        usort($result, function (array $a, array $b) {
            return strcmp($a['value'], $b['value']);
        });

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @return mixed[]
     */
    public function hasProvider(): array
    {
        return [
            ['id', false],
            ['description', false],
            ['test_list', true],
            ['testmetric_amount', false],
            ['testmetric_unit', true],
            ['testmoney_amount', false],
            ['testmoney_currency', true],
            ['country', true],
            ['gender', true],
            ['salary_amount', false],
            ['salary_currency', true],
            ['area_amount', false],
            ['area_unit', true]
        ];
    }

    /**
     * @return mixed[]
     */
    public function nameProvider(): array
    {
        return [
            ['test_list', 'Things.test_list'],
            ['testmetric_unit', 'units_area'],
            ['testmoney_currency', 'currencies'],
            ['country', 'countries'],
            ['gender', 'genders'],
            ['salary_currency', 'currencies'],
            ['title', 'titles'],
            ['language', 'languages']
        ];
    }
}
