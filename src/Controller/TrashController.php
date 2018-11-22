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
     * @return void
     */
    public function index()
    {
        $tableList = [];
        $tables = ConnectionManager::get('default')->schemaCollection()->listTables();
        foreach ($tables as $tableName) {
            $table = TableRegistry::get($tableName);
            if ($table->behaviors()->has('Trash')) {
                $tableClass = preg_replace('@^(.*?)Table$@', '$1', substr(strrchr(get_class($table), "\\"), 1));
                $query = $table->find('onlyTrashed');
                $total = $query->select(['total' => $query->func()->count('*')])->first()->toArray();
                $tableList[] = [
                    'tableName' => $tableName,
                    'controllerName' => str_replace('_', '-', $tableName),
                    'total' => $total['total'],
                    'tableClass' => $tableClass
                ];
            }
        }

        $this->set('table_list', $tableList);
    }
}
