<?php
namespace App\Model\Table;

use App\Feature\Factory as FeatureFactory;
use Cake\Core\App;
use Cake\Utility\Hash;
use CsvMigrations\Table;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * App Model
 */
class AppTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('AuditStash.AuditLog', [
            'blacklist' => ['created', 'modified', 'created_by', 'modified_by']
        ]);

        $tableConfig = (new ModuleConfig(ConfigType::MODULE(), App::shortName(get_class($this), 'Model/Table', 'Table')))->parseToArray();

        if (Hash::get($tableConfig, 'table.searchable')) {
            $fieldsConfig = (new ModuleConfig(ConfigType::MIGRATION(), $this->getAlias()))->parseToArray();

            $this->addBehavior('Search.Searchable', [
                'fields' => array_keys(array_filter($fieldsConfig, function ($definition) {
                    return ! (bool)$definition['non-searchable'];
                }))
            ]);
        }

        if (Hash::get($tableConfig, 'table.lookup_fields')) {
            $this->addBehavior('Lookup', ['lookupFields' => $tableConfig['table']['lookup_fields']]);
        }
    }

    /**
     * Skip setting associations for disabled modules.
     *
     * {@inheritDoc}
     */
    protected function setAssociation(string $type, string $alias, array $options): void
    {
        // skip if associated module is disabled
        if (isset($options['className']) && ! FeatureFactory::get('Module' . DS . $options['className'])->isActive()) {
            return;
        }

        $this->{$type}($alias, $options);
    }
}
