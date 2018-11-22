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
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function restore()
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;

        $table = $this->request->param('pass')[0];
        $id = $this->request->param('pass')[1];

        $toRestore = $this->checkData($table, $id);
        $restore = TableRegistry::get($table)->restoreTrash($toRestore);

        $this->redirect($this->referer());
        if ($restore) {
            $this->Flash->success(__('The record is restored'));

            return;
        }
        $this->Flash->error(__('Can not restore the record. Please, try again.'));
    }

    /**
     * Delete permanently method
     *
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;

        $table = $this->request->param('pass')[0];
        $id = $this->request->param('pass')[1];

        $toDel = $this->checkData($table, $id);
        $delete = TableRegistry::get($table)->removeBehavior('Trash')->delete($toDel);

        $this->redirect($this->referer());
        if ($delete) {
            $this->Flash->success(__('The record is permanently delete'));

            return;
        }
        $this->Flash->error(__('Can not delete the record. Please, try again.'));
    }

    /**
     * Check data
     * @param string $table table to search the id
     * @param uuid $id id to delete
     * @return \Cake\Datasource\EntityInterface|void
     */
    private function checkData($table, $id)
    {
        if (!TableRegistry::get($table)->behaviors()->has('Trash')) {
            throw new \Exception('The table $table has no trashed fields');
        }
        $entity = TableRegistry::get($table)->find('onlyTrashed')->where(['id' => $id])->firstOrFail();

        return $entity;
    }
}
