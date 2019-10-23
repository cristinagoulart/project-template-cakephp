<?php
namespace App\Shell;

use CakeDC\Users\Model\Behavior\SocialBehavior;
use CakeDC\Users\Shell\UsersShell as BaseShell;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\Utility\Validate\Utility;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\ModuleConfig\Parser\Parser;
use Webmozart\Assert\Assert;

class FixDateTimeShell extends BaseShell
{

    /**
     * @var array $modules List of known modules
     */
    protected $modules;

    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Checking for datetime fields to be updated');
        $parser->addOption('module', [
            'short' => 'm',
            'help' => 'Specific module to fix'
        ]);

        $parser->addOption('timezonefrom', ['short' => 'f', 'help' => 'Select the current timezone', 'default' => '']);
        $parser->addOption('timezoneto', ['short' => 't', 'help' => 'Select the new timezone', 'default' => '']);

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @param string $modules Comma-separated list of module names to validate
     * @return bool|int|void
     */
    public function main(string $modules = '')
    {
        $this->info('Checking for datetime fields to be updated');
        $this->hr();

        $this->modules = !empty($this->param('module')) ? (array)$this->param('module') : Utility::getModules();

        if (empty($this->modules)) {
            $this->warn('Did not find any modules');
            exit();
        }

        $modules = '' === $modules ? $this->modules : explode(',', $modules);

        $timezoneFrom = $this->params['timezonefrom'];
        $timezoneTo = $this->params['timezoneto'];

        if (empty($timezoneFrom) || empty($timezoneTo)) {
            $this->abort('Invalid Timezones entered');
        }

        foreach ($modules as $module) {
            $this->checkFields((string)$module);
        }
    }

    /**
     * Execute a check
     *
     * @param string $module Module name
     * @return int Number of encountered errors
     */
    public function checkFields(string $module) : int
    {
        $mc = $this->getModuleConfig($module, []);

        $fields = [];
        $config = json_encode($mc->parse());
        $fields = false === $config ? [] : json_decode($config, true);

        // Check fields
        $this->checkDateTimeFields($module, $fields);

        return count($fields);
    }

    /**
     * Creates a custom instance of `ModuleConfig` with a parser, schema and
     * extra validation.
     *
     * @param string $module Module.
     * @param string[] $options Options.
     * @return ModuleConfig Module Config.
     */
    protected function getModuleConfig(string $module, array $options = []): ModuleConfig
    {
        $configFile = empty($options['configFile']) ? null : $options['configFile'];
        $mc = new ModuleConfig(ConfigType::MIGRATION(), $module, $configFile, ['cacheSkip' => true]);

        $schema = $mc->createSchema(['lint' => true]);
        $mc->setParser(new Parser($schema, ['lint' => true, 'validate' => true]));

        return $mc;
    }

    /**
     * Check fields and their types.
     *
     * @param string $module Module name.
     * @param mixed[] $fields List of field definitions.
     * @return void
     */
    public function checkDateTimeFields(string $module, array $fields = []): void
    {
        $skipfields = ['created', 'modified', 'trashed'];
        $fieldsToUpdate = [];

        $this->info('Trying to update datetime fields for ' . $module);

        $table = TableRegistry::getTableLocator()->get($module);
        $entities = $table->find();

        // Check each field one by one
        foreach ($fields as $field) {
            if (in_array($field['name'], $skipfields)) {
                continue;
            }

            $type = $field['type'];

            switch ($type) {
                case 'datetime':
                    $fieldsToUpdate[] = $field['name'];
                    break;
                default:
                    break;
            }
        }

        $updatedRecords = 0;

        foreach ($entities as $entity) {
            foreach ($fieldsToUpdate as $fieldToUpdate) {
                if (empty($entity->get($fieldToUpdate))) {
                    continue;
                }
                $message = 'Record [' . $entity->get('id') . ']. Value of ' . $fieldToUpdate . ' changed from ' . $entity->get($fieldToUpdate);

                $datetime = new \Cake\I18n\Time($entity->get($fieldToUpdate)->format('Y-m-d H:i:s'), $this->params['timezonefrom']);
                $datetime = $datetime->setTimezone($this->params['timezoneto']);
                $entity->set($fieldToUpdate, $datetime);
                $table->saveOrFail($entity);
                $message .= ' to ' . $entity->get($fieldToUpdate);
                $this->warn($message);
                $updatedRecords++;
            }
        }
        $this->success($updatedRecords . ' record(s) Updated');
        unset($entities);
    }
}
