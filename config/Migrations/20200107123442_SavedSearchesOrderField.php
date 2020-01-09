<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class SavedSearchesOrderField extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(): void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        foreach ($table->find()->all() as $savedSearch) {
            if (in_array($savedSearch->get('order_by_field'), $savedSearch->get('fields'), true)) {
                continue;
            }

            $savedSearch->set('order_by_field', end($savedSearch->get('fields')));

            $table->saveOrFail($savedSearch);
        }
    }
}
