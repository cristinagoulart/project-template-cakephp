<?php
namespace App\Controller;

use CsvMigrations\Controller\AppController as BaseController;

/**
 * ScheduledJobs Controller
 *
 */
class ScheduledJobsController extends BaseController
{
    /**
     * Index method
     *
     * Returns a a list of scheduled jobs
     *
     * @return void|\Cake\Network\Response
     */
    public function index()
    {
    }

    /**
     * Add Scheduled Job instance
     *
     * Saving executing Scheduled Job with RRule params
     *
     * @return \Cake\Http\Response|void|null
     */
    public function add()
    {
        $model = $this->{$this->name};
        $entity = $model->newEntity();

        $commands = $model->getList();

        if ($this->request->is(['post', 'put'])) {
            $entity = $model->patchEntity($entity, $this->request->getData());

            if ($model->save($entity)) {
                $this->Flash->success((string)__('Scheduled Job has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error((string)__('Scheduled Job could not be saved. Please, try again'));
        }

        $this->set(compact('entity', 'commands'));
        $this->set('_serialize', ['entity', 'commands']);
    }

    /**
     * Edit Scheduled Jobs record
     *
     * @param mixed $entityId of the scheduled job
     *
     * @return \Cake\Http\Response|void|null
     */
    public function edit($entityId = null)
    {
        $model = $this->{$this->name};
        $entity = $model->get($entityId, [
            'contain' => [],
        ]);

        $redirectUrl = ['action' => 'view', $entityId];
        $commands = $model->getList();

        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->data('btn_operation') == 'cancel') {
                return $this->redirect($redirectUrl);
            }

            $entity = $model->patchEntity($entity, $this->request->getData());
            $saved = $model->save($entity);

            if ($saved) {
                $this->Flash->success((string)__('The record has been saved.'));
            } else {
                $this->Flash->error((string)__('This record could not be saved.'));
            }

            return $this->redirect($redirectUrl);
        }

        $this->set(compact('entity', 'commands'));
        $this->set('_serialize', ['entity', 'commands']);
    }
}
