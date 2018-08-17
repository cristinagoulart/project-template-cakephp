<?php
namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Validation\Validation;
use CsvMigrations\Controller\AppController as BaseController;

/**
 * ScheduledJobs Controller
 *
 */
class ScheduledJobLogsController extends BaseController
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
     * View method
     *
     * @param string|null $id Entity id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $entity = $this->{$this->name}->find()
            ->where([$this->{$this->name}->getPrimaryKey() => $id])
            ->first();

        if (empty($entity) && ! Validation::uuid($id)) {
            $entity = $this->{$this->name}->find()
                ->applyOptions(['lookup' => true, 'value' => $id])
                ->firstOrFail();
        }

        if (empty($entity)) {
            throw new RecordNotFoundException(sprintf(
                'Record not found in table "%s"',
                $this->{$this->name}->getTable()
            ));
        }

        $this->set('entity', $entity);
        $this->set('_serialize', ['entity']);
    }
}
