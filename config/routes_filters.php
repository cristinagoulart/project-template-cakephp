<?php
use Cake\Routing\Router;

/**
 * URL Filter to redirect requests from DatabaseLog.DatabaseLogs to Logs
 */
Router::addUrlFilter(function ($params, $request) {
    if (empty($params['plugin']) || $params['plugin'] !== 'DatabaseLog' || empty($params['controller']) || $params['controller'] !== 'DatabaseLogs') {
        return $params;
    }
    if ($params['action'] === 'search') {
        $params['controller'] = 'Logs';
        $params['plugin'] = null;
    }

    return $params;
});
