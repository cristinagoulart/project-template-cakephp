<?php
namespace App\Controller\Api\V1\V0;

use Cake\Event\Event;

class LanguageTranslationsController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $this->Crud->on('beforePaginate', function (Event $event) {
            $query = $event->subject()->query;

            $params = $this->request->query;

            if ($this->request->query('object_model') && $this->request->query('object_foreign_key')) {
                $table = $this->loadModel();
                $conditions = [
                    'object_model' => $this->request->query('object_model'),
                    'object_foreign_key' => $this->request->query('object_foreign_key'),
                ];

                if ($this->request->query('object_field')) {
                    $conditions['object_field'] = $this->request->query('object_field');
                }

                if ($this->request->query('language')) {
                    $conditions['language_id'] = $table->getLanguageId($this->request->query('language'));
                }

                $query->applyOptions(['conditions' => $conditions]);
                $query->applyOptions(['contain' => ['Languages']]);
                $query->applyOptions(['fields' => [
                    $table->aliasField('translation'),
                    $table->aliasField('object_model'),
                    $table->aliasField('object_foreign_key'),
                    $table->aliasField('object_field'),
                    'Languages.code'
                ]]);
            } else {
                // In case of missing params to return empty dataset instead of all records
                $query->applyOptions(['conditions' => ['id' => null]]);
            }
        });

        return $this->Crud->execute();
    }
}
