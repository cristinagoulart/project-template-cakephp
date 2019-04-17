<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use DatabaseLog\Controller\Admin\LogsController as BaseController;
use Search\Controller\SearchTrait;
use Search\Utility;
use Search\Utility\Options as SearchOptions;
use Search\Utility\Search;
use Search\Utility\Validator as SearchValidator;

class LogsController extends BaseController
{
    use SearchTrait;

    /**
     * Setup pagination
     *
     * @var array
     */
    public $paginate = [
        'order' => ['DatabaseLogs.id' => 'DESC'],
        'fields' => [
            'DatabaseLogs.created',
            'DatabaseLogs.type',
            'DatabaseLogs.message',
            'DatabaseLogs.id'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * Implement this method to avoid having to overwrite
     * the constructor and call parent.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->paginate['limit'] = 10;
        $this->paginate['fields'] = null;
    }

    /**
     * Delete log records older than specified time (maxLength).
     *
     * This is identical to `./bin/cake database_logs gc` functionality.
     *
     * @return \Cake\Http\Response|void|null
     */
    public function gc()
    {
        $this->request->allowMethod('post');

        $age = Configure::read('DatabaseLog.maxLength');
        if (!$age) {
            $this->Flash->error("Max age is not configured.");

            return $this->redirect(['action' => 'index']);
        }

        $date = new Time($age);
        $count = $this->DatabaseLogs->deleteAll(['created <' => $date]);

        $this->Flash->success('Removed ' . number_format($count) . ' log records older than ' . ltrim($age, '-') . '.');

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Search action
     *
     * @param  string $id Saved search id
     * @return \Cake\Http\Response|void|null
     */
    public function search(string $id = '')
    {
        $model = $this->modelClass;

        /** @var \Search\Model\Table\SavedSearchesTable */
        $searchTable = TableRegistry::get($this->tableName);
        $table = $this->loadModel();
        $search = new Search($table, $this->Auth->user());

        if (!$searchTable->isSearchable($model) && !$this->Auth->user('is_admin')) {
            throw new BadRequestException('You cannot search in ' . implode(' - ', pluginSplit($model)) . '.');
        }

        if ($this->request->is('post')) {
            $searchData = $search->prepareData($this->request);
            if ('' !== $id) {
                $search->update($searchData, $id);
            } else {
                $id = $search->create($searchData);
            }

            list($plugin, $controller) = pluginSplit($model);

            return $this->redirect(['plugin' => $plugin, 'controller' => $controller, 'action' => __FUNCTION__, $id]);
        }

        $entity = $search->get($id);

        $searchData = $entity->get('content');
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->layout('ajax');
            $queryData = $this->getSearchFieldsFromArray((array)$this->request->getQuery());
            $response = $this->getAjaxViewVars($queryData, $table, $search);
            $this->set('types', $this->DatabaseLogs->getTypes());
            $this->set('data', $response);
            $this->set('module', $this->loadModel()->getAlias());

            $this->render('advance_search_result');

            return;
        }

        $searchData = SearchValidator::validateData($table, $searchData['latest'], $this->Auth->user());

        // reset should only be applied to current search id (url parameter)
        // and NOT on newly pre-saved searches and that's we do the ajax
        // request check above, to prevent resetting the pre-saved search.
        $search->reset($entity);

        $savedSearches = $searchTable->find('all')
            ->where([
                'SavedSearches.name IS NOT' => null,
                'SavedSearches.system' => false,
                'SavedSearches.user_id' => $this->Auth->user('id'),
                'SavedSearches.model' => $model
            ])
            ->toArray();

        $this->set('searchableFields', Utility::instance()->getSearchableFields($table, $this->Auth->user()));
        $this->set('savedSearches', $savedSearches);
        $this->set('model', $model);
        $this->set('modelAlias', $this->loadModel()->getAlias());
        $this->set('searchData', $searchData);
        $this->set('savedSearch', $entity);
        $this->set('preSaveId', $search->create($searchData));
        // INFO: this is valid when a saved search was modified and the form was re-submitted
        $this->set('searchOptions', SearchOptions::get());
        $this->set('associationLabels', Utility::instance()->getAssociationLabels($table));

        $this->render($this->searchElement);
    }

    /**
     * Remove unnecessary data from array
     *
     * @param mixed[] $data List of data
     * @return mixed[]
     */
    protected function getSearchFieldsFromArray(array $data): array
    {
        $searchFieldNamesAllowed = [
            'criteria' => null,
            'fields' => null,
            'aggregator' => null,
            'display_columns' => null,
            'sort_by_field' => null,
            'sort_by_order' => null,
            'group_by' => null,
        ];

        return array_intersect_key($data, $searchFieldNamesAllowed);
    }
}
