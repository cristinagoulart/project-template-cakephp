<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Event\Plugin\Search\Model\SearchableFieldsListener;
use App\Feature\Factory as FeatureFactory;
use AuditStash\Meta\RequestMetadata;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RolesCapabilities\CapabilityTrait;
use RuntimeException;
use Search\Controller\SearchTrait;
use Search\Model\Entity\SavedSearch;
use Search\Utility as SearchUtility;
use Search\Utility\Search;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    use CapabilityTrait;
    use ChangelogTrait;
    use SearchTrait;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        $this->loadComponent('Csrf');

        $this->loadComponent('CakeDC/Users.UsersAuth');

        $this->Auth->setConfig('authorize', false);
        $this->Auth->setConfig('loginRedirect', '/');
        $this->Auth->setConfig('flash', ['element' => 'error', 'key' => 'auth']);

        // enable LDAP authentication
        if ((bool)Configure::read('Ldap.enabled')) {
            $this->Auth->config('authenticate', ['Ldap']);
        }

        // prevent access on disabled module
        $feature = FeatureFactory::get('Module' . DS . $this->name);
        if (!$feature->isActive()) {
            throw new NotFoundException();
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|void|null
     */
    public function beforeRender(Event $event)
    {
        // Note: These defaults are just to get started quickly with development
        // and should not be used in production. You should instead set "_serialize"
        // in each action as required.
        // TODO: Adding warning logs and then remove later
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->getType(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }

        $this->set('user', $this->Auth->user());
    }

    /**
     * Callack method.
     *
     * @param  \Cake\Event\Event $event Event object
     * @return \Cake\Http\Response|void|null
     */
    public function beforeFilter(Event $event)
    {
        $this->_allowedResetPassword();

        /**
         * @var \Cake\Controller\Controller $controller
         */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

        // if user not logged in, redirect him to login page
        $url = $request->getAttribute('params');
        try {
            $user = empty($this->Auth->user()) ? [] : $this->Auth->user();
            $result = $this->_checkAccess($url, $user);
            if (!$result) {
                throw new ForbiddenException();
            }
        } catch (ForbiddenException $e) {
            $event->stopPropagation();
            if (empty($this->Auth->user())) {
                $this->Auth->setConfig('authError', false);

                return $this->redirect('/login');
            } else {
                // send empty response for embedded forms
                if ($this->request->query('embedded')) {
                    return $this->response;
                }
                throw new ForbiddenException($e->getMessage(), 0, $e);
            }
        }

        if (method_exists($this, '_getSkipActions')) {
            $this->Auth->allow($this->_getSkipActions($url));
        }

        $this->_setIframeRendering();

        // for audit-stash functionality
        EventManager::instance()->on(new RequestMetadata($this->request, $this->Auth->user('id')));

        $this->_generateApiToken();

        // Load AdminLTE theme
        $this->loadAdminLTE();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $entity = $this->getSystemSearch();
        $searchData = $entity->get('content');

        // return json response and skip any further processing.
        if ($this->request->is('ajax') && $this->request->accepts('application/json')) {
            $this->viewBuilder()->setClassName('Json');
            $response = $this->getAjaxViewVars(
                $searchData['latest'],
                $this->loadModel(),
                new Search($this->loadModel(), $this->Auth->user())
            );
            $this->set($response);

            return;
        }

        $this->set([
            'entity' => $entity,
            'searchData' => $searchData['latest'],
            'preSaveId' => (new Search($this->loadModel(), $this->Auth->user()))->create($searchData['latest']),
            'searchableFields' => SearchableFieldsListener::getSearchableFieldsByTable(
                $this->loadModel(),
                $this->Auth->user()
            ),
            'associationLabels' => SearchUtility::instance()->getAssociationLabels($this->loadModel())
        ]);

        $this->render('/Module/index');
    }

    /**
     * System search getter.
     *
     * @return \Cake\Datasource\EntityInterface
     */
    private function getSystemSearch(): EntityInterface
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');

        $entity = $table->find()
            ->where(['SavedSearches.model' => $this->name, 'SavedSearches.system' => true])
            ->first();

        if ($entity instanceof SavedSearch) {
            return $entity;
        }

        return $this->createSystemSearch();
    }

    /**
     * Creates system search for provided module.
     *
     * @throws \RuntimeException when failed to create system search
     *
     * @return \Cake\Datasource\EntityInterface
     */
    private function createSystemSearch(): EntityInterface
    {
        $table = TableRegistry::getTableLocator()->get('Search.SavedSearches');
        /**
         * @var \Cake\Datasource\EntityInterface $query
         */
        $query = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')
            ->find()
            ->where(['is_superuser' => true])
            ->enableHydration(true)
            ->firstOrFail();

        $user = $query->toArray();

        $id = (new Search($this->loadModel(), $user))->create(['system' => true]);

        $entity = $table->get($id);
        $entity = $table->patchEntity($entity, [
            'name' => sprintf('Default %s search', Inflector::humanize(Inflector::underscore($this->name))),
            'system' => true
        ]);

        if (! $table->save($entity)) {
            throw new RuntimeException(sprintf('Failed to create "%s" system search', $this->name));
        }

        return $entity;
    }

    /**
     * Setup AdminLTE theme
     *
     * This is just to keep the `beforeFilter()` smaller and
     * simpler, as well as to provide extending classes a way
     * to adjust things, if necessary.
     *
     * @return void
     */
    protected function loadAdminLTE(): void
    {
        $loadAdminLTE = true;

        // Skip AdminLTE on JSON requests
        if ($this->request->is('json')) {
            $loadAdminLTE = false;
        }

        // Skip AdminLTE on AJAX requests
        if ($this->request->is('ajax')) {
            $loadAdminLTE = false;
        }

        // Load AdminLTE for regular requests
        if ($loadAdminLTE) {
            $this->viewBuilder()->setClassName('AdminLTE.AdminLTE');
        }

        $this->viewBuilder()->setTheme('AdminLTE');
        $this->viewBuilder()->setLayout('adminlte');

        $title = Inflector::humanize(Inflector::underscore($this->name));
        $mc = new ModuleConfig(ConfigType::MODULE(), $this->name);
        $config = $mc->parse();
        $title = ! empty($config->table->alias) ? $config->table->alias : $title;

        // overwrite theme title before setting the theme
        // NOTE: we set controller specific title, to work around requestAction() calls.
        Configure::write('Theme.title.' . $this->name, $title);
        $this->set('theme', Configure::read('Theme'));
    }

    /**
     * Check if allowed requestResetPassword action is allowed.
     *
     * @return void
     */
    protected function _allowedResetPassword(): void
    {
        $url = [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'requestResetPassword'
        ];

        // skip if url does not match Users requestResetPassword action.
        if (array_diff_assoc($url, $this->request->getAttribute('params'))) {
            return;
        }

        // allow if LDAP is not enabled.
        if (!(bool)Configure::read('Ldap.enabled')) {
            return;
        }

        throw new NotFoundException();
    }

    /**
     * Method that generates API token for internal use.
     *
     * @return void
     */
    protected function _generateApiToken(): void
    {
        Configure::write('API.token', JWT::encode(
            [
                'sub' => $this->Auth->user('id'),
                'exp' => time() + 604800
            ],
            Security::getSalt()
        ));

        Configure::write('CsvMigrations.api.token', Configure::read('API.token'));
        Configure::write(
            'CsvMigrations.BootstrapFileInput.defaults.ajaxSettings.headers.Authorization',
            'Bearer ' . Configure::read('API.token')
        );
        Configure::write('Search.api.token', Configure::read('API.token'));
    }

    /**
     * Allow/Prevent page rendering in iframe.
     *
     * @return void
     */
    protected function _setIframeRendering(): void
    {
        $renderIframe = trim((string)getenv('ALLOW_IFRAME_RENDERING'));

        if ('' !== $renderIframe) {
            $this->setResponse($this->response->withHeader('X-Frame-Options', $renderIframe));
        }
    }

    /**
     * Get list of controller's skipped actions.
     *
     * @param  string $controllerName Controller name
     * @return mixed[]
     */
    public static function getSkipActions(string $controllerName): array
    {
        $result = [
            'getMenu',
            'getCapabilities',
            'getSkipControllers',
            'getSkipActions'
        ];

        return $result;
    }
}
