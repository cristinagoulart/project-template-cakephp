<?php

namespace App\Error;

use CakephpWhoops\Error\WhoopsHandler;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception as CakeException;
use Cake\Routing\Router;

/**
 * Class HttpErrorHandler
 * Application HTTP Error Handler
 * @package App\Error
 */
class HttpErrorHandler extends WhoopsHandler
{
    /**
     * Extends default implementation to add data tables related to CakePHP
     * @param \Exception $exception Caught exception to be handled
     */
    protected function _displayException($exception)
    {
        if (!Configure::read('debug')) {
            parent::_displayException($exception);

            return;
        }

        // Prepare handler with data tables
        $handler = $this->getHandler();

        if ($exception instanceof CakeException) {
            $handler->addDataTable('Cake Exception', $exception->getAttributes());
        }

        $request = Router::getRequest(true);
        $handler->addDataTable('Cake Request', $request->params);

        $whoops = $this->getWhoopsInstance();
        $whoops->pushHandler($handler);
        $whoops->handleException($exception);
    }
}
