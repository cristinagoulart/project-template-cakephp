<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * SoftDelete Controller
 *
 *
 * @method \App\Model\Entity\SoftDelete[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SoftDeleteController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $softDelete = $this->paginate($this->SoftDelete);

        $this->set(compact('softDelete'));
    }

    /**
     * View method
     *
     * @param string|null $id Soft Delete id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $softDelete = $this->SoftDelete->get($id, [
            'contain' => []
        ]);

        $this->set('softDelete', $softDelete);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $softDelete = $this->SoftDelete->newEntity();
        if ($this->request->is('post')) {
            $softDelete = $this->SoftDelete->patchEntity($softDelete, $this->request->getData());
            if ($this->SoftDelete->save($softDelete)) {
                $this->Flash->success(__('The soft delete has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The soft delete could not be saved. Please, try again.'));
        }
        $this->set(compact('softDelete'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Soft Delete id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $softDelete = $this->SoftDelete->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $softDelete = $this->SoftDelete->patchEntity($softDelete, $this->request->getData());
            if ($this->SoftDelete->save($softDelete)) {
                $this->Flash->success(__('The soft delete has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The soft delete could not be saved. Please, try again.'));
        }
        $this->set(compact('softDelete'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Soft Delete id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $softDelete = $this->SoftDelete->get($id);
        if ($this->SoftDelete->delete($softDelete)) {
            $this->Flash->success(__('The soft delete has been deleted.'));
        } else {
            $this->Flash->error(__('The soft delete could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
