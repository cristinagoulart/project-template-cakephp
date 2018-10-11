<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Faker\Factory;
use ReflectionClass;
use ReflectionMethod;

class FakerShell extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->description('Generate fake data.');
        $parser->addArgument('model', [
            'help' => 'Model name to generate fake data for.',
            'required' => true
        ]);
        $parser->addOption('number', [
            'short' => 'n',
            'help' => 'Number of fake records to create.',
            'default' => 10
        ]);

        return $parser;
    }

    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $tableName = $this->args[0];

        $this->out('Generating fake data for [' . $tableName . '] model.');
        $this->hr();

        // get table
        $table = TableRegistry::get($tableName);

        $columns = $table->schema()->columns();

        if (empty($columns)) {
            $this->abort('Table [' . $tableName . '] has no columns.');
        }

        $msg = 'Please select field(s) index number(s) you want to generate data for. Use comma to select multiple fields.';
        $fields = $this->in($this->appendOptions($msg, $columns));

        if (empty($fields)) {
            $this->abort('Aborting, no columns selected.');
        }

        $fields = $this->extractSelected($fields, $columns);

        if (empty($fields)) {
            $this->abort('Aborting, no columns selected.');
        }

        $fields = $this->setFieldFormatter($fields);

        $total = $this->param('number');
        $count = $this->generateFakeData($table, $fields);
        if ($count < $total) {
            $this->err('Only ' . $count . ' out of target ' . $total . ' fake records were created.');
        } else {
            $this->success($count . ' fake records have been created successfully.');
        }
    }

    /**
     * Extract and return selected options from user input.
     * Extracted options number can be limited by passing
     * a value to the optional limit parameter. If limit is
     * set to zero then everything is extracted.
     *
     * @param string $selection User input
     * @param array $options Provided options
     * @param int $limit Limit number of extracted options
     * @return array
     */
    protected function extractSelected($selection, array $options, $limit = 0)
    {
        $result = [];
        $limit = (int)$limit;

        $selection = trim($selection);
        $fields = explode(',', $selection);

        if (empty($fields)) {
            return $result;
        }

        $count = 1;
        foreach ($fields as $field) {
            $field = trim($field) - 1;
            // skip invalid fields
            if (!array_key_exists($field, $options)) {
                continue;
            }

            $result[$options[$field]] = [];
            // return result if limit is reached
            if ($limit !== 0 && $limit === $count) {
                return $result;
            }

            $count++;
        }

        return $result;
    }

    /**
     * Method that interactively retrieves and returns
     * faker field formatters for the selected field(s).
     *
     * @param array $fields Selected field(s)
     * @return array
     */
    protected function setFieldFormatter(array $fields)
    {
        if (empty($fields)) {
            return $fields;
        }

        $providers = $this->getProviders();

        if (empty($providers)) {
            $this->abort('Aborting, no providers found.');
        }

        foreach ($fields as $field => &$formatter) {
            $this->hr();
            $this->out('Setting faker options for [' . $field . '] field.');
            $this->hr();

            $options = array_keys($providers);
            sort($options);
            $provider = $this->in($this->appendOptions('What category applies to:', $options));

            if (empty($provider)) {
                $this->abort('Aborting, no category selected.');
            }

            $provider = $this->extractSelected($provider, $options, 1);

            if (empty($provider)) {
                $this->abort('Aborting, no category selected.');
            }
            // get key as provider
            $provider = key($provider);

            $className = $providers[$provider]['className'];
            $class = new ReflectionClass($className);

            $methods = [];
            foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (!stripos($method->class, $providers[$provider]['shortName'])) {
                    continue;
                }
                $methods[Inflector::underscore($method->name)] = $method->name;
            }

            $options = array_keys($methods);
            sort($options);
            $formatter = $this->in($this->appendOptions('What type of field is:', $options));

            if (empty($formatter)) {
                $this->abort('Aborting, no field type selected.');
            }

            $formatter = $this->extractSelected($formatter, $options, 1);

            if (empty($formatter)) {
                $this->abort('Aborting, no field type selected.');
            }
            // get key as formatter
            $formatter = key($formatter);

            $formatter = $methods[$formatter];
        }

        return $fields;
    }

    /**
     * Append options to interactive message.
     *
     * @param string $message Interactive shell message
     * @param array $options Options to append
     * @return string
     */
    protected function appendOptions($message, array $options)
    {
        $result = $message;
        foreach ($options as $k => $v) {
            $result .= "\n" . ($k + 1) . ': ' . $v;
        }

        return $result;
    }

    /**
     * Retrieves faker supported providers.
     *
     * @return array
     */
    protected function getProviders()
    {
        $generator = Factory::create();

        $result = [];
        foreach ($generator->getProviders() as $provider) {
            $fullClassName = get_class($provider);
            $className = explode('\\', $fullClassName);
            $className = end($className);
            $result[Inflector::underscore($className)] = [
                'className' => $fullClassName,
                'shortName' => $className
            ];
        }

        return $result;
    }

    /**
     * Generates fake data based on selected field(s) and respective
     * formatter(s). Returns count of newly created records.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param array $fields Selected field(s) and formatter(s)
     * @return int
     */
    protected function generateFakeData(Table $table, array $fields)
    {
        $result = 0;
        $total = $this->param('number');
        for ($i = 0; $i < $total; $i++) {
            $faker = Factory::create();
            $data = [];
            foreach ($fields as $k => $v) {
                $data[$k] = $faker->{$v};
            }
            $entity = $table->newEntity();
            $entity = $table->patchEntity($entity, $data);
            if ($table->save($entity)) {
                $result++;
            }
        }

        return $result;
    }
}
