<?php

namespace App\Test\TestCase\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use Webmozart\Assert\Assert;

class ExportShellTest extends ConsoleIntegrationTestCase
{
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

    public function testGc(): void
    {
        $basePath = \App\Service\Export::basePath();
        $folder = new Folder($basePath);

        $this->assertSame([], $folder->find('.*\.csv'));

        $this->exec('export gc');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertSame([], $folder->find('.*\.csv'));
        $this->assertOutputContains('<info>No export file(s) found before ');

        // file created minus one day and one second ago
        $created = touch($basePath . 'export-test-old.csv', time() - 86401);
        Assert::true($created);

        $created = touch($basePath . 'export-test-new.csv');
        Assert::true($created);

        $result = $folder->find('.*\.csv');
        sort($result);
        $this->assertSame(['export-test-new.csv', 'export-test-old.csv'], $result);

        $this->exec('export gc');
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('<success>Successfully deleted 1 export file(s), last modified before ');
        $this->assertSame(['export-test-new.csv'], $folder->find('.*\.csv'));

        unlink($basePath . 'export-test-new.csv');
    }
}
