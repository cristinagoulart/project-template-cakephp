<?php
namespace App\Test\TestCase\Service;

use App\Service\Export;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Webmozart\Assert\Assert;

class ExportTest extends TestCase
{
    public $fixtures = [
        'app.things',
        'app.users',
    ];

    private $path;

    public function setUp(): void
    {
        parent::setUp();

        $this->path = Configure::readOrFail('Export.path');

        Configure::write('Export.path', TMP . 'tests' . DS . 'uploads' . DS . 'export' . DS);
    }

    public function tearDown(): void
    {
        Configure::write('Export.path', $this->path);
        unset($this->path);

        parent::tearDown();
    }

    public function testBasePath(): void
    {
        $this->assertSame(TMP . 'tests' . DS . 'uploads' . DS . 'export' . DS, Export::basePath());
    }

    public function testPath(): void
    {
        $table = TableRegistry::getTableLocator()->get('Things');

        $headers = array_slice($table->getSchema()->columns(), 0, 2);

        $export = Export::fromIds(['00000000-0000-0000-0000-000000000001'], $table, [$table->getDisplayField()], true);
        unlink($export->path());

        $pattern = preg_quote(TMP) . 'tests' . DS . 'uploads' . DS . 'export' . DS . 'Things \- \d+\.csv';
        $this->assertRegExp(
            '#' . $pattern . '#',
            $export->path()
        );
    }

    public function testUrl(): void
    {
        $table = TableRegistry::getTableLocator()->get('Things');

        $export = Export::fromIds(['00000000-0000-0000-0000-000000000001'], $table, [$table->getDisplayField()], true);
        unlink($export->path());

        $pattern = '/uploads/export/Things \- \d+\.csv';
        $this->assertRegExp(
            '#' . $pattern . '#',
            $export->url()
        );
    }

    public function testFromIdsWithoutIds(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $table = TableRegistry::getTableLocator()->get('Things');

        Export::fromIds([], $table, ['name', 'email']);
    }

    public function testFromIdsWithoutFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $table = TableRegistry::getTableLocator()->get('Things');

        Export::fromIds(['00000000-0000-0000-0000-000000000001'], $table, []);
    }

    public function testFromIdsWithNonExistingField(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $table = TableRegistry::getTableLocator()->get('Things');

        Export::fromIds(['00000000-0000-0000-0000-000000000001'], $table, ['a-non-existing-field']);
    }

    /**
     * @param string[] $ids
     * @param string[] $headers
     * @param string[][] $data
     * @dataProvider exportFormattedDataProvider
     */
    public function testFromIds(array $ids, array $headers, array $data): void
    {
        $table = TableRegistry::getTableLocator()->get('Things');

        $allColumns = $table->getSchema()->columns();

        $relatedTable = $table->getAssociation('AssignedToUsers')->getTarget();
        foreach ($relatedTable->getSchema()->columns() as $column) {
            $allColumns[] = $relatedTable->aliasField($column);
        }
        sort($allColumns);

        $export = Export::fromIds($ids, $table, $allColumns, true);

        $csvFileData = self::fetchFromCsvPath($export->path());
        unlink($export->path());

        // remove empty rows
        $csvFileData = array_filter($csvFileData);

        foreach ($headers as $index => $expected) {
            $this->assertSame($expected, $csvFileData[0][$index]);
        }

        foreach ($data as $index => $row) {
            foreach ($row as $key => $expected) {
                $this->assertSame($expected, $csvFileData[$index + 1][$key]);
            }
        }
    }

    /**
     * @param string[] $ids
     * @param string[] $headers
     * @param string[][] $data
     * @dataProvider exportRawDataProvider
     */
    public function testFromIdsInRawFormat(array $ids, array $headers, array $data): void
    {
        $table = TableRegistry::getTableLocator()->get('Things');

        $allColumns = $table->getSchema()->columns();

        $relatedTable = $table->getAssociation('AssignedToUsers')->getTarget();
        foreach ($relatedTable->getSchema()->columns() as $column) {
            $allColumns[] = $relatedTable->aliasField($column);
        }
        sort($allColumns);

        $export = Export::fromIds($ids, $table, $allColumns);

        $csvFileData = self::fetchFromCsvPath($export->path());
        unlink($export->path());

        // remove empty rows
        $csvFileData = array_filter($csvFileData);

        foreach ($headers as $index => $expected) {
            $this->assertSame($expected, $csvFileData[0][$index]);
        }

        foreach ($data as $index => $row) {
            foreach ($row as $key => $expected) {
                $this->assertSame($expected, $csvFileData[$index + 1][$key]);
            }
        }
    }

    /**
     * @return mixed[]
     */
    private static function fetchFromCsvPath(string $path): array
    {
        $fh = fopen($path, 'r');
        Assert::resource($fh);

        $result = [];
        while (! feof($fh)) {
            $result[] = fgetcsv($fh);
        }

        fclose($fh);

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function exportFormattedDataProvider(): array
    {
        return [
            [
                'ids' => ['00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000002'],
                'headers' => ['Activation Date - Users (Assigned To)', 'Active - Users (Assigned To)', 'Additional Data - Users (Assigned To)', 'Api Token - Users (Assigned To)', 'Birthdate - Users (Assigned To)', 'Company - Users (Assigned To)', 'Country - Users (Assigned To)', 'Created - Users (Assigned To)', 'Department - Users (Assigned To)', 'Email - Users (Assigned To)', 'Extras - Users (Assigned To)', 'Fax - Users (Assigned To)', 'First Name - Users (Assigned To)', 'Gender - Users (Assigned To)', 'Id - Users (Assigned To)', 'Image - Users (Assigned To)', 'Initials - Users (Assigned To)', 'Is Superuser - Users (Assigned To)', 'Is Supervisor - Users (Assigned To)', 'Last Name - Users (Assigned To)', 'Modified - Users (Assigned To)', 'Password - Users (Assigned To)', 'Phone Extension - Users (Assigned To)', 'Phone Home - Users (Assigned To)', 'Phone Mobile - Users (Assigned To)', 'Phone Office - Users (Assigned To)', 'Position - Users (Assigned To)', 'Reports To - Users (Assigned To)', 'Role - Users (Assigned To)', 'Secret - Users (Assigned To)', 'Secret Verified - Users (Assigned To)', 'Team - Users (Assigned To)', 'Token - Users (Assigned To)', 'Token Expires - Users (Assigned To)', 'Tos Date - Users (Assigned To)', 'Trashed - Users (Assigned To)', 'Username - Users (Assigned To)', 'Appointment', 'Area Amount', 'Area Unit', 'Assigned To', 'Bio', 'Country', 'Created', 'Created By', 'Currency', 'Date Of Birth', 'label description', 'Email', 'File', 'Gender', 'Id', 'Language', 'Level', 'Modified', 'Modified By', 'label name', 'Non Searchable', 'Phone', 'Photos', 'Primary Thing', 'Rate', 'Salary Amount', 'Salary Currency', 'Sample Date', 'Test List', 'Testmetric Amount', 'Testmetric Unit', 'Testmoney Amount', 'Testmoney Currency', 'Title', 'Trashed', 'Vip', 'Website', 'Work Start'],
                'data' => [
                    ['2015-06-24 17:33:54', '1', '', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2@test.com', '', '', 'user', '', '00000000-0000-0000-0000-000000000002', '', '', '1', '', 'second', '2015-06-24 17:33:54', '', '', '', '', '', '', '', 'admin', 'xxx', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2', '2019-10-29 15:47:16', '25.74', 'm²', 'user-2', 'A blob type', 'Cyprus', '2018-01-18 15:47:16', 'user-1', 'GBP', '1990-01-17', 'Long description goes here', '1@thing.com', '', 'Male', '00000000-0000-0000-0000-000000000001', 'Ancient Greek', '7', '2018-01-18 15:47:16', 'user-1', 'Thing #1', '', '+35725123456', '', 'Thing #2', '25.13', '1000', 'EUR', '', 'first - second children', '33.18', 'ft²', '155.22', 'USD', 'Dr', '', '1', 'https://google.com', '08:32'],
                    ['2015-06-24 17:33:54', '1', '', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2@test.com', '', '', 'user', '', '00000000-0000-0000-0000-000000000002', '', '', '1', '', 'second', '2015-06-24 17:33:54', '', '', '', '', '', '', '', 'admin', 'xxx', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2', '', '25', 'm²', 'user-2', '', '', '2018-01-18 15:47:16', 'user-1', '', '', 'Long description goes here', '2@thing.com', '', '', '00000000-0000-0000-0000-000000000002', '', '', '2018-01-18 15:47:16', 'user-1', 'Thing #2', '', '', '', 'Thing #1', '', '1000', 'EUR', '', '', '', '', '', '', '', '', '', '', ''],
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function exportRawDataProvider(): array
    {
        return [
            [
                'ids' => ['00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000002'],
                'headers' => ['Activation Date - Users (Assigned To)', 'Active - Users (Assigned To)', 'Additional Data - Users (Assigned To)', 'Api Token - Users (Assigned To)', 'Birthdate - Users (Assigned To)', 'Company - Users (Assigned To)', 'Country - Users (Assigned To)', 'Created - Users (Assigned To)', 'Department - Users (Assigned To)', 'Email - Users (Assigned To)', 'Extras - Users (Assigned To)', 'Fax - Users (Assigned To)', 'First Name - Users (Assigned To)', 'Gender - Users (Assigned To)', 'Id - Users (Assigned To)', 'Image - Users (Assigned To)', 'Initials - Users (Assigned To)', 'Is Superuser - Users (Assigned To)', 'Is Supervisor - Users (Assigned To)', 'Last Name - Users (Assigned To)', 'Modified - Users (Assigned To)', 'Password - Users (Assigned To)', 'Phone Extension - Users (Assigned To)', 'Phone Home - Users (Assigned To)', 'Phone Mobile - Users (Assigned To)', 'Phone Office - Users (Assigned To)', 'Position - Users (Assigned To)', 'Reports To - Users (Assigned To)', 'Role - Users (Assigned To)', 'Secret - Users (Assigned To)', 'Secret Verified - Users (Assigned To)', 'Team - Users (Assigned To)', 'Token - Users (Assigned To)', 'Token Expires - Users (Assigned To)', 'Tos Date - Users (Assigned To)', 'Trashed - Users (Assigned To)', 'Username - Users (Assigned To)', 'Appointment', 'Area Amount', 'Area Unit', 'Assigned To', 'Bio', 'Country', 'Created', 'Created By', 'Currency', 'Date Of Birth', 'label description', 'Email', 'File', 'Gender', 'Id', 'Language', 'Level', 'Modified', 'Modified By', 'label name', 'Non Searchable', 'Phone', 'Photos', 'Primary Thing', 'Rate', 'Salary Amount', 'Salary Currency', 'Sample Date', 'Test List', 'Testmetric Amount', 'Testmetric Unit', 'Testmoney Amount', 'Testmoney Currency', 'Title', 'Trashed', 'Vip', 'Website', 'Work Start'],
                'data' => [
                    ['2015-06-24 17:33:54', '1', '', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2@test.com', '', '', 'user', '', '00000000-0000-0000-0000-000000000002', '', '', '1', '', 'second', '2015-06-24 17:33:54', '', '', '', '', '', '', '', 'admin', 'xxx', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2', '2019-10-29 15:47:16', '25.74', 'm', '00000000-0000-0000-0000-000000000002', 'A blob type', 'CY', '2018-01-18 15:47:16', '00000000-0000-0000-0000-000000000001', 'GBP', '1990-01-17', 'Long description goes here', '1@thing.com', '', 'm', '00000000-0000-0000-0000-000000000001', 'grc', '7', '2018-01-18 15:47:16', '00000000-0000-0000-0000-000000000001', 'Thing #1', '', '+35725123456', '', '00000000-0000-0000-0000-000000000002', '25.13', '1000', 'EUR', '', 'first.second_children', '33.18', 'ft', '155.22', 'USD', 'Dr', '', '1', 'https://google.com', '08:32'],
                    ['2015-06-24 17:33:54', '1', '', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2@test.com', '', '', 'user', '', '00000000-0000-0000-0000-000000000002', '', '', '1', '', 'second', '2015-06-24 17:33:54', '', '', '', '', '', '', '', 'admin', 'xxx', '', '', '', '', '2015-06-24 17:33:54', '', 'user-2', '', '25', 'm', '00000000-0000-0000-0000-000000000002', '', '', '2018-01-18 15:47:16', '00000000-0000-0000-0000-000000000001', '', '', 'Long description goes here', '2@thing.com', '', '', '00000000-0000-0000-0000-000000000002', '', '', '2018-01-18 15:47:16', '00000000-0000-0000-0000-000000000001', 'Thing #2', '', '', '', '00000000-0000-0000-0000-000000000001', '', '1000', 'EUR', '', '', '', '', '', '', '', '', '', '', ''],
                ],
            ],
        ];
    }
}
