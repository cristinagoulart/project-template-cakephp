<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * LogActions component
 */
class LogActionsComponent extends Component
{
    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeFilter(Event $event) : void
    {
        $controllers = Configure::read('LogActions.controllers');
        $actions = Configure::read('LogActions.actions');

        if (empty($controllers) || empty($actions)) {
            return;
        }

        if (!in_array($this->request->getParam('controller'), $controllers) && !in_array($this->request->getParam('action'), $actions)) {
            return;
        }

        Configure::read('LogActions.log_to_file') ? $this->logToFile() :$this->logToDb();
    }

    /**
     * Log in LogAudid table
     *
     * @return void
     */
    protected function logToDb() : void
    {
        $table = TableRegistry::getTableLocator()->get('LogAudit');
        $user_id = $this->_registry->getController()->Auth->user('id');

        $meta = [
            'ip' => $this->request->clientIp(),
            'user' => $user_id,
            'url' => $this->request->here(),
            'action' => $this->request->getParam('action'),
            'pass' => empty($this->request->getParam('pass')[0]) ? '' : $this->request->getParam('pass')[0]
        ];

        $data = [
            'timestamp' => Time::parse('now'),
            'primary_key' => empty($this->request->getParam('pass')[0]) ? '' : $this->request->getParam('pass')[0],
            'source' => $this->request->getParam('controller'),
            'user_id' => $user_id,
            'meta' => json_encode($meta)
        ];

        $table->save(new Entity($data));
    }

    /**
     * Log to file
     * https://en.wikipedia.org/wiki/Common_Log_Format
     *
     * @return void
     */
    protected function logToFile() : void
    {
        $date = Time::now();
        $folder = new Folder(ROOT . DS . 'logs' . DS . $date->i18nFormat('yyyy_MM'), true);
        $file = new File($folder->pwd() . DS . $date->i18nFormat('yyyy_MM_dd'), true);

        $ip = $this->request->clientIp();
        $user_id = $this->_registry->getController()->Auth->user('id');
        $url = $this->request->here();
        $now = $date->i18nFormat('dd/MMM/yyyy:HH:mm:ss');

        $log = sprintf('%s %s - [%s] "%s" - -' . PHP_EOL, $ip, $user_id, $now, $url);
        $file->append($log, true);
    }
}
