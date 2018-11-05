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

    /**
     * Give access to edit any user settings.
     * @param string $context uuid of user
     * @return null
     */
    public function user($context)
    {
        $this->scope = 'user';
        $this->context = $context;
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
        // Filter the Configure::read('Settings') with User Roles
        $dataSettings = Configure::read('Settings');
        $dataFiltered = TableRegistry::get('Settings')->filterSettings($dataSettings, [$this->scope]);

        $settings = $this->paginate($this->Settings);
        $this->set(compact('settings'));
        $this->set('data', $dataFiltered);

        if ($this->request->is('put')) {
            $dataPut = Hash::flatten($this->request->data('Settings'));
            $query = TableRegistry::get('Settings');
            $type = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.type');
            $scope = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.scope');

            $set = [];
            foreach ($dataPut as $key => $value) {
                $entity = $query->findByKey($key)->firstOrFail();
                $params = [
                    'key' => $key,
                    'value' => $value,
                    'scope' => $this->scope,
                    'context' => $this->context,
                    // dynamic field to pass type to the validator
                    'type' => $type[$key]
                ];
                $newEntity = $this->Settings->patchEntity($entity, $params);
                $set[] = $newEntity;
            }

            if ($query->saveMany($set)) {
                Configure::load('Settings', 'dbconfig', true);
                $this->Flash->success(__('Settings successfully updated'));
            } else {
                $this->Flash->error(__('Failed to update settings, please try again.'));
            }
        }
    }

    /**
     * Pass data to generator page
     * @return \Cake\Http\Response|array
     */
    public function generator()
    {
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

            return var_export($this->request->data());
        }
    }
}
