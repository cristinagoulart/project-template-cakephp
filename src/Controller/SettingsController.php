<?php
namespace App\Controller;

use App\Controller\AppController;
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

    private $scope = 'user';
    private $context = '';
    private $dataSettings;

    // Data from the DB with scope 'app'
    private $dataApp;

    // TableRegistry::get('Settings');
    private $query;

    // instead Configure::read(), it will load form the DB the settings of each scope/contex
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
              ->where(['scope' => 'app', 'context' => 'app'])
              ->toArray();
    }

    /**
     * Give access to edit any user settings.
     * @param string $context uuid of user
     * @return null
     */
    public function user($context)
    {
        $this->scope = 'user';
        $this->context = $context;
        $dataUser = $this->query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
              ->where(['scope' => 'user', 'context' => $this->context])
              ->toArray();
        $this->configureValue = Hash::merge($this->dataApp, $dataUser);
        $this->dataSettings = Hash::merge($this->dataSettings, Hash::expand($this->dataApp), Hash::expand($dataUser));
        $this->viewBuilder()->template('index');

        return $this->settings();
    }

    /**
     * Give access to edit app settings
     * @return null
     */
    public function app()
    {
        $this->scope = 'app';
        $this->context = 'app';
        $this->configureValue = $this->dataApp;
        $this->viewBuilder()->template('index');

        return $this->settings();
    }

    /**
     * Give access to edit personal settings
     * @return null
     */
    public function my()
    {
        $this->scope = 'user';
        $this->context = $this->Auth->user('id');
        $dataUser = $this->query->find('list', ['keyField' => 'key', 'valueField' => 'value'])
              ->where(['scope' => 'user', 'context' => $this->context])
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
            $links = Hash::filter(Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.links'));

            $set = [];
            foreach ($dataPut as $key => $value) {
                $entity = $this->createEntity($key, $value, $type[$key]);
                !empty($entity) ? ($set[] = $entity) : '';

                if (empty($links[$key])) {
                    continue;
                }

                foreach ($links[$key] as $link => $keyLink) {
                    $entity = $this->createEntity($keyLink, $value, $type[$key]);
                    !empty($entity) ? ($set[] = $entity) : '';
                }
            }

            // dd($set);

            if ($this->query->saveMany($set)) {
                $this->Flash->success(__('Settings successfully updated'));

                return $this->redirect($this->here);
            } else {
                $this->Flash->error(__('Failed to update settings, please try again.'));
            }
        }
    }

    /**
     * if the key exist in the DB, will create and validate an entity.
     * @param  string $key   key
     * @param  string $value value
     * @param  string $type  type
     * @return \App\Model\Entity\Setting|void
     */
    private function createEntity($key, $value, $type)
    {
        // if the key doesn't exist it fails.
        $entity = $this->query->findByKey($key)->firstOrFail();
        // select based on key, scope, conext
        $entity = $this->query->find('all')->where(['key' => $key, 'scope' => $this->scope, 'context' => $this->context])->first();

        // will storage only the modified settings
        if (!is_null($entity) && $entity->value === $value) {
            // if the user setting match the app setting, the entity will be deleted
            if ($this->scope === 'user' && $value === $this->dataApp[$key]) {
                $this->query->delete($entity);
            }

            return;
        }

        $params = [
            'key' => $key,
            'value' => $value,
            'scope' => $this->scope,
            'context' => $this->context,
            // dynamic field to pass type to the validator
            'type' => $type
        ];

        // if (entity not exist) : new ? patch
        $newEntity = is_null($entity) ? $this->Settings->newEntity($params) : $this->Settings->patchEntity($entity, $params);

        return $newEntity;
    }

    /**
     * Pass data to generator page
     * Avaiable only for developers in localhost
     * @return \Cake\Http\Response|void|arra
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
        $this->set('scope', ['app', 'user']);

        if ($this->request->is('post')) {
            $this->autoRender = false;
            // debug format much better that var_export
            debug($this->request->data());

            return;
        }
    }
}
