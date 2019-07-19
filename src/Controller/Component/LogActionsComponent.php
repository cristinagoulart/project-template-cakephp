<?php
namespace App\Controller\Component;

use App\Event\AuditViewEvent;
use AuditStash\PersisterInterface;
use AuditStash\Persister\ElasticSearchPersister;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Webmozart\Assert\Assert;

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
        $user_id = $this->_registry->getController()->Auth->user('id');

        $controller = $this->getController();
        $request = $controller->request;
        $table = $this->getController()->loadModel();
        Assert::isInstanceOf($table, Table::class);

        $meta = [
            /**
             * The following details must be auto-completed by AuditStash.beforeLog
            'ip' => $request->clientIp(),
            'user' => $user_id,
            'url' => $request->getRequestTarget(),
             */
            'action' => $request->getParam('action'),
            'pass' => empty($request->getParam('pass')[0]) ? '' : $request->getParam('pass')[0]
        ];

        /**
         * Event details are passed when creating the event
        $event = [
            'timestamp' => Time::parse('now'),
            'primary_key' => empty($request->getParam('pass')[0]) ? '' : $request->getParam('pass')[0],
            'source' => $request->getParam('controller'),
            'user_id' => $user_id,
            'meta' => json_encode($meta)
        ];
         */

        $primary = empty($request->getParam('pass')[0]) ? '' : $request->getParam('pass')[0];

        $event = new AuditViewEvent(Text::uuid(), $primary, $table->getAlias(), [], []);
        $event->setMetaInfo($meta);

        $data = $controller->dispatchEvent('AuditStash.beforeLog', ['logs' => [$event]]);
        $this->getPersister()->logEvents($data->getData('logs'));
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

    /**
     * Initiates a new persister object to use for logging view audit events.
     *
     * @return PersisterInterface The configured persister
     */
    private function getPersister(): PersisterInterface
    {
        $class = Configure::read('AuditStash.persister') ?: ElasticSearchPersister::class;
        $index = $this->getConfig('index') ?: $this->getController()->loadModel();
        $type = $this->getConfig('type') ?: Inflector::singularize($index);

        return new $class(compact('index', 'type'));
    }
}
