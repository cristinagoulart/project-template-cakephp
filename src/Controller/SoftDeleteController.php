<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * SoftDelete Controller
 */
class SoftDeleteController extends AppController
{
    /**
     * Restore record
     *
     * @param string $table table to search the id
     * @param uuid $id id to delete
     * @return bool Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function restore()
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        $id = $this->request->data('id');
        $table = $this->request->data('table');

        $toRestore = $this->checkData($table, $id);
        $restore = TableRegistry::get($table)->restoreTrash($toRestore);

        if ($restore) {
            $this->Flash->success(__('The record is restored'));

            return true;
        }
        $this->Flash->error(__('Can not restore the record. Please, try again.'));

        return false;
    }

    /**
     * Delete permanently method
     *
     * @param string $table table to search the id
     * @param uuid $id id to delete
     * @return bool Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        $id = $this->request->data('id');
        $table = $this->request->data('table');

        $toDel = $this->checkData($table, $id);
        $delete = TableRegistry::get($table)->removeBehavior('Trash')->delete($toDel);
        
        if ($delete) {
            $this->Flash->success(__('The record is permanently delete'));

            return true;
        }
        $this->Flash->error(__('Can not delete the record. Please, try again.'));

        return false;
    }

    /**
     * Check data
     * @param string $table table to search the id
     * @param uuid $id id to delete
     * @return \Cake\Datasource\EntityInterface|bool
     * @throws \Exception
     */
    private function checkData($table, $id)
    {
        if (!TableRegistry::exists($table)) {
            throw new \Exception('Table $table not found');
        }
        if (!TableRegistry::get($table)->behaviors()->has('Trash')) {
            throw new \Exception('The table $table has no trashed fields');
        }
        $entity = TableRegistry::get($table)->find('onlyTrashed')->where(['id' => $id])->firstOrFail();

        return $entity;
    }
}
