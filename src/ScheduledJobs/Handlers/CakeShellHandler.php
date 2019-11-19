<?php

namespace App\ScheduledJobs\Handlers;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;

class CakeShellHandler extends AbstractHandler
{
    /**
     * @var string $prefix for differentiating jobs
     */
    protected $prefix = 'CakeShell';

    /**
     * Get List of Shells
     *
     * Code is pretty much taken from CommandsShell of CakePHP core
     *
     * @param mixed[] $options with configs passed if any
     *
     * @return mixed[] $result with associated array of plugins and its commands.
     */
    public function getList(array $options = []): array
    {
        $result = [];

        $config = Configure::read('Cron.' . $this->prefix);

        $skipFiles = !empty($config['skipFiles']) ? $config['skipFiles'] : [];
        $skipPlugins = !empty($config['skipPlugins']) ? $config['skipPlugins'] : [];

        $plugins = Plugin::loaded();
        $plugins = array_diff((array)$plugins, $skipPlugins);

        $shellList = array_fill_keys($plugins, null) + ['CORE' => null, 'app' => null];

        $appPath = App::path('Shell');
        $appShells = $this->scanDir($appPath[0]);
        $appShells = array_diff($appShells, $skipFiles);

        $shellList = $this->appendShells('app', $appShells, $shellList);

        foreach ($plugins as $plugin) {
            $pluginPath = Plugin::classPath($plugin) . 'Shell';
            $pluginShells = $this->scanDir($pluginPath);
            $shellList = $this->appendShells($plugin, $pluginShells, $shellList);
        }

        $shellList = array_filter($shellList);

        // flatting command list
        foreach ($shellList as $plugin => $shells) {
            foreach ($shells as $name) {
                $result[] = $this->prefix . '::' . ucfirst($plugin) . ':' . $name;
            }
        }

        // sorting shells alphabetically
        asort($result);

        // fixing array indexing
        $result = array_values($result);

        return $result;
    }

    /**
     * Scan the provided paths for shells, and append them into $shellList
     *
     * @param string $type The type of object.
     * @param string[] $shells The shell name.
     * @param mixed[] $shellList List of shells.
     * @return mixed[] The updated $shellList
     */
    protected function appendShells(string $type, array $shells, array $shellList): array
    {
        foreach ($shells as $shell) {
            $shellList[$type][] = Inflector::underscore(str_replace('Shell', '', $shell));
        }

        return $shellList;
    }

    /**
     * Scan a directory for .php files and return the class names that
     * should be within them.
     *
     * @param string $dir The directory to read.
     * @return string[] The list of shell classnames based on conventions.
     */
    protected function scanDir(string $dir): array
    {
        $dir = new Folder($dir);
        $contents = $dir->read(true, true);
        if (empty($contents[1])) {
            return [];
        }
        $shells = [];
        foreach ($contents[1] as $file) {
            if (substr($file, -4) !== '.php') {
                continue;
            }
            $shells[] = substr($file, 0, -4);
        }

        return $shells;
    }
}
