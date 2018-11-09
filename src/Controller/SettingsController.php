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

        $userName = TableRegistry::get('Users')->find('list')->where(['id' => $context])->toArray();
        $this->set('afterTitle', ' » ' . $userName[$context]);

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

        if ($this->isLocalhost()) {
            $this->set('afterTitle', ' » System <h4><a href=/settings/generator/>settings.php file builder utility</a></h4>');
        }

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

        $this->set('afterTitle', ' » ' . $this->Auth->user('username'));

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
                $entity = $this->query->createEntity($key, $value, $type[$key], $this->scope, $this->context);
                !empty($entity) ? ($set[] = $entity) : '';

                if (empty($links[$key])) {
                    continue;
                }

                foreach ($links[$key] as $link => $keyLink) {
                    $entity = $this->query->createEntity($keyLink, $value, $type[$key], $this->scope, $this->context);
                    !empty($entity) ? ($set[] = $entity) : '';
                }
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
     * ONLY for developers
     * Pass data to generator page
     * Avaiable only for developers in localhost
     * @return \Cake\Http\Response|void|arra
     * @throws UnauthorizedException check if is localhost
     */
    public function generator()
    {
        if (!$this->isLocalhost()) {
            throw new UnauthorizedException('Run in localhost to access');
        }

        // For render the main structure
        $dataSettings = Configure::read('Settings');
        $this->set('data', empty($dataSettings) ? null : $dataSettings);
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

    /**
     * Check if the webserver is on localhost
     * @return bool true if is localhost
     */
    private function isLocalhost()
    {
        $localhost = [
            '127.0.0.1',
            '::1'
        ];

        if (!in_array($_SERVER['REMOTE_ADDR'], $localhost)) {
            return false;
        }

        return true;
    }
}
