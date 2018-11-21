<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class TrashController extends AppController
{
    /**
     * Display the tables that support trash
     */
    public function index()
    {
        $table_list = [];
        $tables = ConnectionManager::get('default')->schemaCollection()->listTables();
        foreach ($tables as $table_name) {
            $table = TableRegistry::get($table_name);
            if ($table->behaviors()->has('Trash')) {
                $table_class = preg_replace('@^(.*?)Table$@', '$1', substr(strrchr(get_class($table), "\\"), 1));
                $query = $table->find('onlyTrashed');
                $total = $query->select(['total' => $query->func()->count('*')])->first()->toArray();
                $table_list[] = [
                    'tableName' => $table_name,
                    'controllerName' => str_replace('_', '-', $table_name),
                    'total' => $total['total'],
                    'tableClass' => $table_class
                ];
            }
        }

        $this->set('table_list', $table_list);
    }
}
