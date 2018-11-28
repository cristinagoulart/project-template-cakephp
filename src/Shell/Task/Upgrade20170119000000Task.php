<?php
namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use DirectoryIterator;
use RuntimeException;
use SplFileInfo;

class Upgrade20170119000000Task extends Shell
{

    /**
     * Module directories
     *
     * @var array $moduleDirs Directories each module must have
     */
    protected $moduleDirs = [
        'db',
        'config',
        'lists',
        'views',
    ];

    /**
     * Get option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Upgrade CsvMigrations to Modules');

        return $parser;
    }

    /**
     * Shell entry point
     *
     * @return void
     */
    public function main()
    {
        $src = ROOT . DS . 'config' . DS . 'CsvMigrations';

        if (!file_exists($src)) {
            $this->info("Source path [$src] does not exist. Nothing to do.");

            return;
        }

        $this->out("Upgrading file paths in [$src]");
        $this->upgrade($src);
        $this->out("All done");
    }

    /**
     * Validate source folder
     *
     * @throws \RuntimeException when $src does not exist or is not a directory
     * @param string $src Path to source folder
     * @return void
     */
    protected function validateSource(string $src): void
    {
        if (!is_dir($src)) {
            throw new RuntimeException("Source path [$src] is not a directory");
        }
    }

    /**
     * Create module folders
     *
     * @throws \RuntimeException when folder creation fails
     * @param string $dst Destination folder path
     * @param string $module Module name
     * @return void
     */
    protected function createModuleFolders(string $dst, string $module): void
    {
        // Prepend destination and module to module directories
        $dirs = array_map(function ($a) use ($dst, $module) {
            return $dst . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $a;
        }, $this->moduleDirs);

        // Add module's parent directory to the top of the list
        array_unshift($dirs, $dst . DIRECTORY_SEPARATOR . $module);

        foreach ($dirs as $dir) {
            // Skip if directory already exists
            if (file_exists($dir) && is_dir($dir)) {
                continue;
            }
            $result = mkdir($dir);
            if (!$result) {
                throw new RuntimeException("Failed to create [$dir]");
            }
        }
    }

    /**
     * Remove given folder
     *
     * @throws \RuntimeException if removal fails
     * @param string $dst Path to folder to remove
     * @return void
     */
    protected function removeFolder(string $dst): void
    {
        if (!file_exists($dst)) {
            return;
        }
        $result = rmdir($dst);
        if (!$result) {
            throw new RuntimeException("Failed to remove [$dst]");
        }
    }

    /**
     * Move files from given to source to destination
     *
     * @throws \RuntimeException when failed to move file
     * @param string $src Path to source folder
     * @param string $dst Path to destination folder
     * @param mixed[] $files Optional list of files to move (all, if empty)
     * @return void
     */
    protected function moveFiles(string $src, string $dst, array $files = []): void
    {
        if (!file_exists($src)) {
            return;
        }

        if (empty($files)) {
            $files = new DirectoryIterator($src);
        }
        foreach ($files as $file) {
            // Convert SplFileInfo objects to file names
            if ($file instanceof SplFileInfo) {
                $file = $file->getFilename();
            }
            // Skip dot files
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $srcFile = $src . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($srcFile)) {
                continue;
            }
            $dstFile = $dst . DIRECTORY_SEPARATOR . $file;
            $result = rename($srcFile, $dstFile);
            if (!$result) {
                throw new RuntimeException("Failed moving [$srcFile] to [$dstFile]");
            }
        }
    }

    /**
     * Upgrade given path
     *
     * @throws \RuntimeException when failed to create destination folder
     * @param string $src Path to folder to upgrade
     * @return void
     */
    protected function upgrade(string $src): void
    {
        $this->validateSource($src);

        $dst = dirname($src) . DIRECTORY_SEPARATOR . 'Modules';
        if (!file_exists($dst)) {
            $result = mkdir($dst);
            if (!$result) {
                throw new RuntimeException("Failed to create directory [$dst]");
            }
        }

        $this->createModuleFolders($dst, 'Common');

        // Move all lists into Common module
        $this->out("Moving all lists into Common module");
        $srcDir = $src . 'lists';
        $dstDir = $dst . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'lists';
        $this->moveFiles($srcDir, $dstDir);
        $this->removeFolder($srcDir);

        // Move all views files
        $this->out("Moving all views files");
        $viewsDir = $src . 'views';
        if (file_exists($viewsDir)) {
            $dir = new DirectoryIterator($viewsDir);
            foreach ($dir as $moduleDir) {
                if ($moduleDir->isDot()) {
                    continue;
                }
                $moduleName = $moduleDir->getFilename();
                $this->createModuleFolders($dst, $moduleName);

                $srcDir = $viewsDir . DIRECTORY_SEPARATOR . $moduleName;
                $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views';
                $this->moveFiles($srcDir, $dstDir);
                $this->removeFolder($srcDir);
            }
            $this->removeFolder($viewsDir);
        }

        // Move all migration files
        $this->out("Moving all migration files");
        $migrationsDir = $src . DIRECTORY_SEPARATOR . 'migrations';
        if (file_exists($migrationsDir)) {
            $dir = new DirectoryIterator($migrationsDir);
            foreach ($dir as $moduleDir) {
                if ($moduleDir->isDot()) {
                    continue;
                }

                $moduleName = $moduleDir->getFilename();
                $this->createModuleFolders($dst, $moduleName);

                // migration.csv goes into db/ folder
                $srcDir = $migrationsDir . DIRECTORY_SEPARATOR . $moduleName;
                $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'db';
                $this->moveFiles($srcDir, $dstDir, ['migration.csv']);

                // everything else goes into config/ folder
                $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'config';
                $this->moveFiles($srcDir, $dstDir);

                $this->removeFolder($srcDir);
            }
            $this->removeFolder($migrationsDir);
        }
        $this->removeFolder($src);
    }
}
