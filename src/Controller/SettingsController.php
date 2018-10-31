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

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        // Get User Roles
        $capabilities = TableRegistry::get('RolesCapabilities.Capabilities');
        $userGroups = $capabilities->getUserGroups($this->Auth->user('id'));
        $userRoles = $capabilities->getGroupsRoles($userGroups);

        // Filter the Configure::read('Settings') with User Roles
        $dataSettings = Configure::read('Settings');
        $dataFiltered = TableRegistry::get('Settings')->filterSettings($dataSettings, $userRoles);

        $settings = $this->paginate($this->Settings);
        $this->set(compact('settings'));
        $this->set('data', $dataFiltered);

        if ($this->request->is('put')) {
            $dataPut = Hash::flatten($this->request->data('Settings'));
            $query = TableRegistry::get('Settings');
            $type = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.type');
            $roles = Hash::combine($dataFiltered, '{s}.{s}.{s}.{s}.alias', '{s}.{s}.{s}.{s}.roles');

            $set = [];
            foreach ($dataPut as $key => $value) {
                // check the roles (never trust the user input)
                if (count(array_intersect($roles[$key], $userRoles)) === 0) {
                    $this->Flash->error(__('Failed to update settings, please try again.'));
                    throw new UnauthorizedException('Can not update');
                }
                $entity = $query->findByKey($key)->firstOrFail();
                $params = [
                    'key' => $key,
                    'value' => $value,
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
     * @return null Nothing to return
     */
    public function generator()
    {
        // For render the main structure
        $dataSettings = Configure::read('Settings');
        $this->set('data', $dataSettings);

        // For seach the new fields to insert
        $data = Hash::flatten(Configure::read());
        $this->set('alldata', $data);

        // list of roles
        $capabilities = TableRegistry::get('QoboRoles')->find('list', ['keyField' => 'name'])->toArray();
        $this->set('roles', array_keys($capabilities));
    }

    /**
     * Return a php array for render in html
     * @return null Nothing to return
     */
    public function returnArray()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            echo '<pre>';
            var_export($this->request->data());
            echo '</pre>';
        }
    }
}
