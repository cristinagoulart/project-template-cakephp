<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class AdjustGroupedSavedSearches extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up() : void
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        foreach ($table->find()->where(['group_by !=' => ''])->all() as $savedSearch) {
            $data = [
                'fields' => [$savedSearch->get('group_by'), sprintf('COUNT(%s)', $savedSearch->get('group_by'))]
            ];
            $table->patchEntity($savedSearch, $data);

            $table->saveOrFail($savedSearch);
        }
    }
}
