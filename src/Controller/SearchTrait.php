<?php
namespace App\Controller;

use App\Search\Manager as SearchManager;
use App\Utility\BasicSearch;
use App\Utility\Search;
use App\Utility\SearchOptions;
use App\Utility\SearchUtility;
use App\Utility\SearchValidator;
use Cake\Core\Configure;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Search\Event\EventName as SearchEventName;
use Search\Utility\Export;

trait SearchTrait
{
    /**
     * Search action
     *
     * @param  string $id Saved search id
     * @return \Cake\Http\Response|void|null
     */
    public function search(string $id = '')
    {
        $table = $this->loadModel();

        if (! $table->hasBehavior('Searchable')) {
            throw new BadRequestException(sprintf('Search is not available for %s', $table->getAlias()));
        }

        $manager = new SearchManager($table, $this->Auth->user());

        $search = new Search($table, $this->Auth->user());

        // redirect on POST requests (PRG pattern)
        if ($this->request->is('post')) {
            $searchData = $this->getRequest()->getData();

            if ('' !== $id) {
                $search->update($searchData, $id);
            }

            if ('' === $id) {
                $id = $search->create($searchData);
            }

            list($plugin, $controller) = pluginSplit($this->modelClass);

            return $this->redirect([
                'plugin' => $this->getRequest()->getParam('plugin'),
                'controller' => $this->getRequest()->getParam('controller'),
                'action' => $this->getRequest()->getParam('action'),
                $id
            ]);
        }

        $entity = $search->get($id);

        // return json response and skip any further processing.
        if ($this->request->is('get') && $this->request->is('ajax') && $this->request->accepts('application/json')) {
            $this->viewBuilder()->setClassName('Json');
            $response = $this->gerResponse(Hash::get($entity->get('content'), 'latest'));
            $this->set($response);

            return;
        }

        $this->set('preSaveId', $search->create(Hash::get($entity->get('content'), 'latest')));

        $this->render('/Module/search-new');
    }

    /**
     * Get AJAX response view variables
     *
     * @param mixed[] $searchData Search data
     * @return mixed[] Variables and values for AJAX response
     */
    private function gerResponse(array $searchData): array
    {
        $table = $this->loadModel();
        $queryParams = $this->request->getQueryParams();

        $manager = new SearchManager($table, $this->Auth->user());
        $options = $manager->getOptionsFromRequest($searchData, $queryParams);

        $query = $table->find('search', $options);

        $resultSet = $this->paginate($query);

        return [
            'success' => true,
            'data' => SearchManager::resultSetFormatter(
                $resultSet,
                $table,
                $this->Auth->user(),
                Hash::get($searchData, 'group_by')
            ),
            'pagination' => ['count' => $resultSet->count()],
            '_serialize' => ['success', 'data', 'pagination']
        ];
    }

    /**
     * Method that handles search result-set.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\ORM\ResultSet $entities ResultSet
     * @param \Cake\ORM\Table $table Table instance
     *
     * @return void
     */
    public function afterFind(Event $event, ResultSet $entities, Table $table): void
    {
        if ($entities->isEmpty()) {
            return;
        }

        $fhf = new FieldHandlerFactory();

        foreach ($entities as $entity) {
            /**
             * @var \Cake\Datasource\EntityInterface $entity
             */
            $entity = $entity;
            $this->_renderValues($entity, $table, $fhf);
        }

        $event->result = $entities;
    }

    /**
     * Passes search entity fields through Field Handlers.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @param \CsvMigrations\FieldHandlers\FieldHandlerFactory $fhf Field Handler Factory
     *
     * @return void
     */
    protected function _renderValues(EntityInterface $entity, Table $table, FieldHandlerFactory $fhf): void
    {
        foreach ($entity->visibleProperties() as $prop) {
            if ('_matchingData' === $prop) {
                foreach ($entity->{$prop} as $associationName => $targetEntity) {
                    /**
                     * @var \Cake\ORM\Association $association
                     */
                    $association = $table->association($associationName);
                    $targetTable = $association->getTarget();
                    $this->_renderValues($targetEntity, $targetTable, $fhf);
                }
            } else {
                $entity->{$prop} = $fhf->renderValue($table, $prop, $entity->{$prop});
            }
        }
    }
}
