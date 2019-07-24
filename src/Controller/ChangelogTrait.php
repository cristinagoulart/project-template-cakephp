<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Webmozart\Assert\Assert;

/**
 * Controller Trait responsible for changelog functionality.
 */
trait ChangelogTrait
{
    /**
     * Table name for Log Audit model.
     *
     * @var string
     */
    protected $_tableLog = 'LogAudit';

    /**
     * Element to be used as View template.
     *
     * @var string
     */
    protected $_elementView = '/Element/changelog';

    /**
     * Return log audit results for specific record.
     *
     * @param  string $id Record id
     * @return \Cake\Http\Response|void|null
     */
    public function changelog(string $id)
    {
        /*
        ideally we want to group by user and timestamp, but having user in the meta information makes this non-trivial
        for now we are using just the timestamp assuming that different will edit the same record at the same time is
        very unlikely.
         */
        $query = TableRegistry::get($this->_tableLog)->find('all')
            ->where(['primary_key' => $id, 'source' => Inflector::underscore($this->name)])
            ->select(['timestamp', 'user_id', 'original', 'changed', 'type'])
            ->order(['timestamp' => 'DESC'])
            ->group('timestamp');

        $table = $this->loadModel();
        Assert::isInstanceOf($table, Table::class);

        $modelAlias = $table->getAlias();
        $methodName = 'moduleAlias';
        if (method_exists($this->loadModel(), $methodName) && is_callable([$this->loadModel(), $methodName])) {
            $modelAlias = $this->loadModel()->{$methodName}();
        }

        $entity = $table->find()
            ->where(['id' => $id])
            ->enableHydration(true)
            ->firstOrFail();

        $this->set('changelog', $this->paginate($query));
        $this->set('modelAlias', $modelAlias);
        $this->set('displayField', $table->getDisplayField());
        $this->set('usersTable', TableRegistry::get(Configure::read('Users.table')));
        $this->set('entity', $entity);

        $this->render($this->_elementView);

        $this->set('_serialize', ['changelog']);
    }
}
