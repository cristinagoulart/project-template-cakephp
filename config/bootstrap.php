<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Configure paths required to find CakePHP + general filepath
 * constants
 */
require __DIR__ . '/paths.php';

// Use composer to load the autoloader.
require ROOT . DS . 'vendor' . DS . 'autoload.php';

/**
 * Bootstrap CakePHP.
 *
 * Does the various bits of setup that CakePHP needs to do.
 * This includes:
 *
 * - Registering the CakePHP autoloader.
 * - Setting the default application paths.
 */
require CORE_PATH . 'config' . DS . 'bootstrap.php';

// You can remove this if you are confident you have intl installed.
if (!extension_loaded('intl')) {
    trigger_error('You must enable the intl extension to use CakePHP.', E_USER_ERROR);
}

use App\Event\Component\UserIdentifyListener;
use App\Event\Controller\Api\AddActionListener;
use App\Event\Controller\Api\EditActionListener;
use App\Event\Controller\Api\IndexActionListener;
use App\Event\Controller\Api\LookupActionListener;
use App\Event\Controller\Api\RelatedActionListener;
use App\Event\Controller\Api\ViewActionListener;
use App\Event\Model\LookupListener;
use App\Event\Plugin\CsvMigrations\Controller\BatchActionListener;
use App\Event\Plugin\CsvMigrations\FieldHandlers\MagicDefaultValueListener;
use App\Event\Plugin\CsvMigrations\Model\DatabaseListsListener;
use App\Event\Plugin\Menu\View\AccessMenuListener;
use App\Event\Plugin\Menu\View\ApplicationMenuListener;
use App\Event\Plugin\Menu\View\DashboardMenuListener;
use App\Event\Plugin\Menu\View\ModuleMenuListener;
use App\Event\Plugin\Search\Model\ChildListItemsListener;
use App\Event\Plugin\Search\Model\ReportsListener;
use App\Event\Plugin\Search\Model\SearchableFieldsListener;
use App\Event\Plugin\Search\Model\SearchResultsListener;
use App\Feature\Factory as FeatureFactory;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use CakephpWhoops\Error\WhoopsHandler;
use Cake\Cache\Cache;
use Cake\Console\ConsoleErrorHandler;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Core\Plugin;
use Cake\Database\Type;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventManager;
use Cake\Log\Log;
use Cake\Network\Email\Email;
use Cake\Network\Request;
use Cake\Routing\DispatcherFactory;
use Cake\Utility\Inflector;
use Cake\Utility\Security;

/**
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
    Configure::load('avatar', 'default');
    Configure::load('csv_migrations', 'default');
    Configure::load('database_log', 'default');
    Configure::load(file_exists(CONFIG . 'features_local.php') ? 'features_local' : 'features', 'default');
    Configure::load('file_storage', 'default');
    Configure::load('groups', 'default');
    Configure::load('icons', 'default');
    Configure::load('menu', 'default');
    Configure::load('roles_capabilities', 'default');
    Configure::load('cron', 'default');
} catch (\Exception $e) {
    die($e->getMessage() . "\n");
}

// Load an environment local configuration file.
// You can use a file like app_local.php to provide local overrides to your
// shared configuration.
//Configure::load('app_local', 'default');

// When debug = false the metadata cache should last
// for a very very long time, as we don't want
// to refresh the cache while users are doing requests.
if (!Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+1 years');
    Configure::write('Cache._cake_core_.duration', '+1 years');
}

/**
 * Set server timezone to UTC. You can change it to another timezone of your
 * choice but using UTC makes time calculations / conversions easier.
 */
date_default_timezone_set('UTC');

/**
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Configure::read('App.encoding'));

/**
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', 'en_US');

/**
 * Register application error and exception handlers.
 */
$isCli = php_sapi_name() === 'cli';
if ($isCli) {
    (new ConsoleErrorHandler(Configure::read('Error')))->register();
} else {
    (new WhoopsHandler(Configure::read('Error')))->register();
}

// Include the CLI bootstrap overrides.
if ($isCli) {
    require __DIR__ . '/bootstrap_cli.php';
}

/**
 * Set the full base URL.
 * This URL is used as the base of all absolute links.
 *
 * If you define fullBaseUrl in your config file you can remove this.
 */
if (!Configure::read('App.fullBaseUrl')) {
    $s = null;
    if (env('HTTPS')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');
    if (isset($httpHost)) {
        Configure::write('App.fullBaseUrl', 'http' . $s . '://' . $httpHost);
    }
    unset($httpHost, $s);
}

// Optionally stop using the now redundant default loggers
Log::drop('debug');
Log::drop('error');

// Configure::consume() reads and deletes the value.
// This is useful for consistency and security reasons.
Cache::config(Configure::consume('Cache'));
ConnectionManager::config(Configure::consume('Datasources'));
Log::config(Configure::consume('Log'));
Security::salt(Configure::consume('Security.salt'));

// Read, rather than consume, since we have some logic that
// needs to know if email sending is enabled or not.
// See `src/Shell/EmailShell.php` for example, but also in
// plugins.
Email::configTransport(Configure::read('EmailTransport'));
Email::config(Configure::read('Email'));

/**
 * The default crypto extension in 3.0 is OpenSSL.
 * If you are migrating from 2.x uncomment this code to
 * use a more compatible Mcrypt based implementation
 */
// Security::engine(new \Cake\Utility\Crypto\Mcrypt());

/**
 * Setup detectors for mobile and tablet.
 */
Request::addDetector('mobile', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isMobile();
});
Request::addDetector('tablet', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isTablet();
});

/**
 * Custom Inflector rules, can be set to correctly pluralize or singularize
 * table, model, controller names or whatever other string is passed to the
 * inflection functions.
 *
 * Inflector::rules('plural', ['/^(inflect)or$/i' => '\1ables']);
 * Inflector::rules('irregular' => ['red' => 'redlings']);
 * Inflector::rules('uninflected', ['dontinflectme']);
 * Inflector::rules('transliteration', ['/å/' => 'aa']);
 */

/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. make sure you read the documentation on Plugin to use more
 * advanced ways of loading plugins
 *
 * Plugin::loadAll(); // Loads all plugins at once
 * Plugin::load('Migrations'); //Loads a single plugin named Migrations
 *
 */
Plugin::load('Qobo/Utils', ['bootstrap' => true]);
Plugin::load('Migrations');
Plugin::load('CsvMigrations', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Crud');
Plugin::load('Groups', ['bootstrap' => true, 'routes' => true]);
Plugin::load('RolesCapabilities', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Menu', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Translations', ['routes' => true, 'bootstrap' => true]);
Plugin::load('AuditStash');
Plugin::load('DatabaseLog', ['routes' => true]);
Plugin::load('Search', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Burzum/FileStorage');
if (Configure::read('Swagger.crawl') && Configure::read('API.auth')) {
    Plugin::load('Alt3/Swagger', ['bootstrap' => true, 'routes' => true]);
}
Plugin::load('AdminLTE', ['bootstrap' => true, 'routes' => true]);

// Only load JwtAuth plugin if API authentication is enabled
if (Configure::read('API.auth')) {
    Plugin::load('ADmad/JwtAuth');
}

/**
 * @todo seems like if CakeDC/Users plugin is loaded
 * before any of our plugins that use routes, it breaks
 * them, needs to be investigated further.
 */
Configure::write('Users.config', ['users']);
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);

/**
 * Connect middleware/dispatcher filters.
 */
DispatcherFactory::add('Asset');
DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');

EventManager::instance()->on(new AddActionListener());
EventManager::instance()->on(new BatchActionListener());
EventManager::instance()->on(new EditActionListener());
EventManager::instance()->on(new ChildListItemsListener());
EventManager::instance()->on(new DatabaseListsListener());
EventManager::instance()->on(new IndexActionListener());
// @link https://github.com/burzum/cakephp-file-storage/blob/master/docs/Documentation/Included-Event-Listeners.md
EventManager::instance()->on(new LocalListener([
    'imageProcessing' => true,
    'pathBuilderOptions' => [
        'pathPrefix' => Configure::read('FileStorage.pathBuilderOptions.pathPrefix')
    ]
]));
EventManager::instance()->on(new LookupActionListener());
EventManager::instance()->on(new LookupListener());
EventManager::instance()->on(new MagicDefaultValueListener());
EventManager::instance()->on(new ApplicationMenuListener());
EventManager::instance()->on(new DashboardMenuListener());
EventManager::instance()->on(new ModuleMenuListener());
EventManager::instance()->on(new AccessMenuListener());
EventManager::instance()->on(new RelatedActionListener());
EventManager::instance()->on(new ReportsListener());
EventManager::instance()->on(new SearchableFieldsListener());
EventManager::instance()->on(new SearchResultsListener());
EventManager::instance()->on(new UserIdentifyListener());
EventManager::instance()->on(new ViewActionListener());

// load AdminLTE theme settings
Configure::load('admin_lte', 'default');

// load system information settings
Configure::load('system_info', 'default');

// Feature Factory initialization
// IMPORTANT: this line should be placed at the end of the bootstrap file.
FeatureFactory::init();

/**
 * Register custom database type(s)
 */
Type::map('base64', 'App\Database\Type\EncodedFileType');
