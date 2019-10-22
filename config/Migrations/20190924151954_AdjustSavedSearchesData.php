<?php
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Migrations\AbstractMigration;
use Search\Model\Entity\SavedSearch;
use Webmozart\Assert\Assert;

class AdjustSavedSearchesData extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up() : void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        foreach ($table->find()->all() as $savedSearch) {
            $table->patchEntity($savedSearch, $this->normalizedData($savedSearch));
            $table->saveOrFail($savedSearch);
        }
    }

    /**
     * Normalizes data to match table's new schema.
     *
     * @param \Search\Model\Entity\SavedSearch $savedSearch SavedSearch
     * @return array
     */
    private function normalizedData(SavedSearch $savedSearch) : array
    {
        Assert::isArray($savedSearch->get('content'));

        return [
            'conjunction' => (string)Hash::get($savedSearch->get('content'), 'saved.aggregator'),
            'criteria' => (array)Hash::get($savedSearch->get('content'), 'saved.criteria'),
            'fields' => (array)Hash::get($savedSearch->get('content'), 'saved.display_columns'),
            'group_by' => (string)Hash::get($savedSearch->get('content'), 'saved.group_by'),
            'order_by_field' => (string)Hash::get($savedSearch->get('content'), 'saved.sort_by_field'),
            'order_by_direction' => strtoupper((string)Hash::get($savedSearch->get('content'), 'saved.sort_by_order'))
        ];
    }
}
