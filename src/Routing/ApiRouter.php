<?php
namespace App\Routing;

use Cake\Core\App;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;
use Qobo\Utils\Utility;

/**
 * ApiRouter Class
 *
 * Class responsible for setting up fallback
 * API routes, including API-versioned routes.
 *
 * Both `api/:controller/:action` and `api/v1.0/:controller/:action` would
 * point to the same `V1/V0` sub-namespace.
 *
 * API versions codebase is located within subdirs in attempt to isolate the
 * business logic of the application.
 *
 */
class ApiRouter
{
    /** @var array $_versions */
    protected $_versions = [];

    /**
     * Default constructor
     *
     * @return void
     */
    public function __construct()
    {
        $apiPath = App::path('Controller/Api')[0];
        $this->_versions = Utility::getApiVersions($apiPath);
    }

    /**
     * Setting Router API prefixes and nested API versiones
     *
     * @return void
     */
    public function setRoutes()
    {
        $versions = $this->_versions;
        $default = 'api/v1/v0';

        Router::scope('/api', function ($routes) use ($versions, $default) {
            // Setting up fallback non-versioned API url calls.
            // It can handle `api/controller/index.json` as well
            // as `api/controller.json` calls.
            Router::prefix('api', function ($routes) use ($default) {

                $routes->setExtensions(['json']);
                $routes->connect('/:controller', ['prefix' => $default], ['routeClass' => DashedRoute::class]);
                $routes->connect('/:controller/:action/*', ['prefix' => $default], ['routeClass' => DashedRoute::class]);
                $routes->fallbacks(DashedRoute::class);
            });

            foreach ($versions as $version) {
                Router::prefix($version['prefix'], ['path' => $version['path']], function ($routes) {
                    $routes->setExtensions(['json']);
                    $routes->fallbacks(DashedRoute::class);
                });
            }
        });
    }
}
