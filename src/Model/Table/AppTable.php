<?php
namespace App\Model\Table;

use App\Feature\Factory as FeatureFactory;
use CsvMigrations\Table;

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
