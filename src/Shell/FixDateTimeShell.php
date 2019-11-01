<?php
namespace App\Shell;

use CakeDC\Users\Model\Behavior\SocialBehavior;
use CakeDC\Users\Shell\UsersShell as BaseShell;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\Utility\Validate\Utility;
use Exception;
use InvalidArgumentException;
use Migrations\AbstractMigration;
use PDOException;
use Phinx\Db\Adapter\MysqlAdapter;
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
        $parser->addOption('limit', ['short' => 'l', 'help' => 'Select number of records per/table', 'default' => '100']);

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

            return false;
        }

        $modules = '' === $modules ? $this->modules : explode(',', $modules);

        $timezoneFrom = $this->params['timezonefrom'];
        $timezoneTo = $this->params['timezoneto'];

        if (empty($timezoneFrom) || empty($timezoneTo)) {
            $this->abort('Invalid Timezones entered');
        }

        foreach ($modules as $module) {
            $this->updateFields((string)$module);
        }
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
     * @param string $module Module name
     * @return void
     */
    public function updateFields(string $module) : void
    {
        $mc = $this->getModuleConfig($module, []);

        $fields = [];
        $config = json_encode($mc->parse());
        $fields = $mc->parseToArray();

        $skipfields = ['created', 'modified', 'trashed'];

        $fieldsToUpdate = [];

        // Check each field one by one to find datetime type fields
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

        // Update fields if exist
        if (!empty($fieldsToUpdate)) {
            $this->updateDateTimeFields($module, $fieldsToUpdate);
        }
    }

    /**
     * Check fields and their types.
     *
     * @param string $module Module name.
     * @param mixed[] $fieldsToUpdate List of field definitions.
     * @return void
     */
    public function updateDateTimeFields(string $module, array $fieldsToUpdate = []): void
    {
        $this->info('Trying to update datetime fields for ' . $module);

        $table = TableRegistry::getTableLocator()->get($module);
        $entities = $table->find()->limit($this->params['limit']);

        $updatedRecords = 0;

        foreach ($entities as $entity) {
            $updatedRecords = $updatedRecords + (int)$this->updateEntity($entity, $table, $fieldsToUpdate, $module);
        }
        $this->success($updatedRecords . ' record(s) updated for ' . $module);
        unset($entities);
    }

    /**
     * [updateEntity description]
     * @param  [type] $entity         [description]
     * @param  [type] $table          [description]
     * @param  [type] $fieldsToUpdate [description]
     * @param  [type] $module         [description]
     * @return [type]                 [description]
     */
    private function updateEntity($entity, $table, $fieldsToUpdate, $module) : int
    {
        $primaryKey = $table->getPrimaryKey();
        $tableQuery = $table->query();

        $updatedRecord = false;

        $dateTimeFixTable = TableRegistry::getTableLocator()->get('datetime_fix');

        //Find if the record to be updated has already been updated and skip the update
        $recordInDateTimeFixTable = $dateTimeFixTable->find()
            ->where(['record_id = ' => $entity->get($primaryKey), 'updated = ' => true, 'module = ' => $module])
            ->first();

        //Skip record if it is already updated
        if (!empty($recordInDateTimeFixTable)) {
            $this->info('Skipping record [' . $entity->get($primaryKey) . '] as it has already been updated.');
            return (int)$updatedRecord;
        }

        foreach ($fieldsToUpdate as $fieldToUpdate) {
            if (empty($entity->get($fieldToUpdate))) {
                continue;
            }

            $message = 'Record [' . $entity->get($primaryKey) . ']. Value of ' . $fieldToUpdate . ' changed from ' . $entity->get($fieldToUpdate);
            $datetime = new \Cake\I18n\Time($entity->get($fieldToUpdate)->format('Y-m-d H:i:s'), $this->params['timezonefrom']);
            $datetime = $datetime->setTimezone($this->params['timezoneto']);

            /*
             Updating records with the query builder will not trigger events such as Model.afterSave.
             */
            $tableQuery->update()
            ->set([$fieldToUpdate => $datetime])
            ->where(['id' => $entity->get($primaryKey)])->limit(1)
            ->execute();

            $message .= ' to ' . $datetime;
            $this->info($message);

            //Proceed updating the datetime_fix table
            $dateTimeFixData = [
                'module' => $module,
                'record_id' => $entity->get($primaryKey),
                'updated' => true
            ];

            $createDateTimeFixRecord = $dateTimeFixTable->newEntity();
            $createDateTimeFixRecord = $dateTimeFixTable->patchEntity($createDateTimeFixRecord, $dateTimeFixData);
            $dateTimeFixTable->saveOrFail($createDateTimeFixRecord);

            $updatedRecord = true;
        }

        unset($recordInDateTimeFixTable);

        return (int)$updatedRecord;
    }
}
