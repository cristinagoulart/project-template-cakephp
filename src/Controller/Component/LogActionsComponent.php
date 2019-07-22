<?php
namespace App\Controller\Component;

use App\Event\AuditReadEvent;
use AuditStash\PersisterInterface;
use AuditStash\Persister\ElasticSearchPersister;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
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
        if (!$this->request->is('get')) {
            return;
        }

        $controllers = Configure::read('LogActions.controllers');
        $actions = Configure::read('LogActions.excludeActions');

        if (empty($controllers)) {
            return;
        }

        if (in_array($this->request->getParam('controller'), $controllers) && in_array($this->request->getParam('action'), $actions)) {
            return;
        }

        $controller = $this->getController();
        $request = $controller->request;
        $table = $this->getController()->loadModel();
        Assert::isInstanceOf($table, Table::class);

        $primary = empty($request->getParam('pass')[0]) ? 'index' : $request->getParam('pass')[0];

        $event = new AuditReadEvent(Text::uuid(), $primary, strtolower($table->getAlias()), [], []);

        $data = $controller->dispatchEvent('AuditStash.beforeLog', ['logs' => [$event]]);
        $this->getPersister()->logEvents($data->getData('logs'));
    }

    /**
     * Initiates a new persister object to use for logging view audit events.
     *
     * @return PersisterInterface The configured persister
     */
    private function getPersister(): PersisterInterface
    {
        $class = Configure::read('AuditStash.persister') ?: ElasticSearchPersister::class;
        $index = $this->getConfig('index') ?: $this->getController()->loadModel()->getAlias();
        $type = $this->getConfig('type') ?: Inflector::singularize($index);

        return new $class(compact('index', 'type'));
    }
}
