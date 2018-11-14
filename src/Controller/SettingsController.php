<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\SettingsTable;
use Cake\Core\Configure;
use Cake\Network\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 *
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SettingsController extends AppController
{

    /**
     * Implemented scope are : user, app
     * @var string
     */
    private $scope;

    /**
     * Value of the scope. In case of :
     * user => uuid
     * app  => SettingsTable::SCOPE_APP
     * @var string
     */
    private $context = '';

    /**
     * It will read the current user setting from Configure::read()
     * or load from the settings table, in case of SettingsTable::SCOPE_APP or other user settings
     * @var array
     */
    private $dataSettings;

    /**
     * Data from the DB with scope SettingsTable::SCOPE_APP
     * @var array
     */
    private $dataApp;

    /**
     * TableRegistry::get('Settings');
     * @var App\Model\Table\SettingsTable
     */
    private $query;

    /**
     * Instead Configure::read(), it will load form the DB the settings of each scope/contex
     * if the user doesn't have a record for a particular key, it will use the app value.
     * @var array
     */
    private $configureValue;

    /**
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->dataSettings = Configure::read('Settings');
        $this->query = TableRegistry::get('Settings');
        $this->dataApp = $this->query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
              ->where(['scope' => SettingsTable::SCOPE_APP, 'context' => SettingsTable::CONTEXT_APP])
              ->toArray();
    }

    /**
     * Give access to edit any user settings.
     * @param string $context uuid of user
     * @return \Cake\Http\Response|void|null
     */
    public function user($context)
    {
        $this->scope = SettingsTable::SCOPE_USER;
        $this->context = $context;
        $dataUser = $this->query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
              ->where(['scope' => SettingsTable::SCOPE_USER, 'context' => $this->context])
              ->toArray();
        $this->configureValue = Hash::merge($this->dataApp, $dataUser);
        $this->dataSettings = Hash::merge($this->dataSettings, Hash::expand($this->dataApp), Hash::expand($dataUser));
        $this->viewBuilder()->template('index');

        return $this->settings();
    }

    /**
     * Give access to edit app settings
     * @return \Cake\Http\Response|void|null
     */
    public function app()
    {
        $this->scope = SettingsTable::SCOPE_APP;
        $this->context = SettingsTable::CONTEXT_APP;
        $this->configureValue = $this->dataApp;
        $this->viewBuilder()->template('index');

        return $this->settings();
    }

    /**
     * Give access to edit personal settings
     * @return \Cake\Http\Response|void|null
     */
    public function my()
    {
        $this->scope = SettingsTable::SCOPE_USER;
        $this->context = $this->Auth->user('id');
        $dataUser = $this->query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
              ->where(['scope' => SettingsTable::SCOPE_USER, 'context' => $this->context])
              ->toArray();
        $this->configureValue = Hash::merge($this->dataApp, $dataUser);
        $this->viewBuilder()->template('index');

        return $this->settings();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    private function settings()
    {
        $dataFiltered = $this->query->filterSettings($this->dataSettings, [$this->scope]);
        $settings = $this->paginate($this->Settings);
        $this->set(compact('settings'));
        $this->set('data', $dataFiltered);
        $this->set('configure', $this->configureValue);

        if ($this->request->is('put')) {
            $dataPut = Hash::flatten($this->request->data('Settings'));
            $this->query = TableRegistry::get('Settings');
            $type = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.type');
            $scope = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.scope');

            $set = [];
            foreach ($dataPut as $key => $value) {
                // select based on key, scope, conext
                $entity = $this->query->find('all')->where(['key' => $key, 'scope' => $this->scope, 'context' => $this->context])->first();

                // will storage only the modified settings
                if (!is_null($entity) && $entity->value === $value) {
                    // if the user setting match the app setting, the entity will be deleted
                    if ($this->scope === SettingsTable::SCOPE_USER && $value === $this->dataApp[$key]) {
                        $this->query->delete($entity);
                    }
                    continue;
                }

                $params = [
                    'key' => $key,
                    'value' => $value,
                    'scope' => $this->scope,
                    'context' => $this->context,
                    // dynamic field to pass type to the validator
                    'type' => $type[$key]
                ];

                // if (entity not exist) : new ? patch
                $newEntity = is_null($entity) ? $this->Settings->newEntity($params) : $this->Settings->patchEntity($entity, $params);
                $set[] = $newEntity;
            }

            if (empty($set)) {
                $this->Flash->success(__('Nothing to update'));

                return $this->redirect($this->here);
            }

            if ($this->query->saveMany($set)) {
                $this->Flash->success(__('Settings successfully updated'));

                return $this->redirect($this->here);
            } else {
                $this->Flash->error(__('Failed to update settings, please try again.'));
            }
        }
    }

    /**
     * Pass data to generator page
     * Avaiable only for developers in localhost
     * @return \Cake\Http\Response|void|array
     */
    public function generator()
    {
        $localhost = [
            '127.0.0.1',
            '::1'
        ];

        if (!in_array($_SERVER['REMOTE_ADDR'], $localhost)) {
            return;
        }

        // For render the main structure
        $dataSettings = Configure::read('Settings');
        $this->set('data', $dataSettings);
        // For seach the new fields to insert
        $data = Hash::flatten(Configure::read());
        $this->set('alldata', $data);

        // list of scope
        $this->set('scope', [SettingsTable::SCOPE_USER, SettingsTable::SCOPE_APP]);

        if ($this->request->is('post')) {
            $this->autoRender = false;

            var_export($this->request->data());
        }
    }
}
