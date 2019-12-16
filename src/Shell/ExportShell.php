<?php

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Webmozart\Assert\Assert;

final class ExportShell extends Shell
{

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('gc', [
            'help' => 'Garbage collector.',
        ]);

        return $parser;
    }

    /**
     * Export files garbage collection.
     *
     * @return void
     */
    public function gc(): void
    {
        $paths = self::exportPaths();
        $paths = array_unique($paths);

        foreach ($paths as $path) {
            $this->gcPath($path);
        }
    }

    /**
     * Removes export files older than -1 day.
     *
     * @param string $path Export path
     * @return void
     */
    private function gcPath(string $path): void
    {
        $targetTime = (new \DateTimeImmutable('-1 day'));

        $folder = new Folder($path);

        $count = 0;
        foreach ($folder->find('.*\.csv') as $file) {
            $file = new File($folder->pwd() . DS . $file);
            if ($file->lastChange() >= $targetTime->getTimestamp()) {
                continue;
            }

            if ($file->delete()) {
                $count++;
            }
        }

        0 === $count ?
            $this->info(sprintf('No export file(s) found before %s', $targetTime->format('Y-m-d H:i:s'))) :
            $this->success(sprintf(
                'Successfully deleted %s export file(s), last modified before %s',
                $count,
                $targetTime->format('Y-m-d H:i:s')
            ));
    }

    /**
     * Returns export directory paths.
     *
     * @return string[]
     */
    private static function exportPaths(): array
    {
        $url = Configure::readOrFail('Search.export.url');
        Assert::string($url);
        $url = trim($url, DS);
        Assert::stringNotEmpty($url);

        return [
            WWW_ROOT . $url . DS,
            \App\Service\Export::path(),
        ];
    }
}
