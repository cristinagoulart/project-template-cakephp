<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use DatabaseLog\Controller\Admin\LogsController as BaseController;

class LogsController extends BaseController
{
    /**
     * Setup pagination
     *
     * @var array
     */
    public $paginate = [
        'order' => ['DatabaseLogs.id' => 'DESC'],
        'fields' => [
            'DatabaseLogs.created',
            'DatabaseLogs.type',
            'DatabaseLogs.message',
            'DatabaseLogs.id'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * Implement this method to avoid having to overwrite
     * the constructor and call parent.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->paginate['limit'] = 10;
        $this->paginate['fields'] = null;
        $this->modelClass = 'DatabaseLog.DatabaseLogs';
    }

    /**
     * Delete log records older than specified time (maxLength).
     *
     * This is identical to `./bin/cake database_logs gc` functionality.
     *
     * @return \Cake\Http\Response|null
     */
    public function gc()
    {
        $this->request->allowMethod('post');

        $age = Configure::read('DatabaseLog.maxLength');
        if (!$age) {
            $this->Flash->error("Max age is not configured.");

            return $this->redirect(['action' => 'index']);
        }

        $date = new Time($age);
        $count = $this->DatabaseLogs->deleteAll(['created <' => $date]);

        $this->Flash->success('Removed ' . number_format($count) . ' log records older than ' . ltrim($age, '-') . '.');

        return $this->redirect(['action' => 'index']);
    }
}
