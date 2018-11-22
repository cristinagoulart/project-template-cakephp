<?php
namespace App\Event\Plugin\Search\Model;

use Cake\Datasource\RepositoryInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Search\Event\EventName;

class ChildListItemsListener implements EventListenerInterface
{
    /**
     * DBlist constant
     */
    const LIST_TYPE_DBLIST = 'dblist';

    /**
     * File list constant
     */
    const LIST_TYPE_FILELIST = 'list';

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_SEARCH_CHILD_ITEMS() => 'childItemsForParent',
        ];
    }

    /**
     * childItemsForParent method
     *
     * @param \Cake\Event\Event $event Event instance
     * @param mixed[] $criteria to build where statement
     *
     * @return mixed[]
     */
    public function childItemsForParent(Event $event, array $criteria): array
    {
        if (empty($criteria['criteria'])) {
            return $criteria;
        }

        foreach ($criteria['criteria'] as $key => $val) {
            $items = [];

            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    if ($v['operator'] != 'in') {
                        continue;
                    }

                    array_push($items, $v['value']);

                    $ret = $this->processChildren($v['value'], $v['type'], $key);

                    if (!empty($ret)) {
                        $items = array_merge($items, $ret);
                    }

                    $criteria['criteria'][$key][$k]['value'] = $items;
                }
            }
        }

        return $criteria;
    }

    /**
     * getDbListChildren method
     *
     * @param string $parentId of parent item
     * @param \Cake\Datasource\RepositoryInterface $table where lists are stored
     *
     * @return mixed[]
     */
    private function getDbListChildren(string $parentId, RepositoryInterface $table): array
    {
        $query = $table->find('all', [
            'conditions' => ['parent_id' => $parentId],
        ]);
        $children = $query->toArray();

        return $children;
    }

    /**
     * getFileListChildren method
     *
     * @param string $parentValue of parent item
     * @param string $listName for target list
     * @return mixed[] with children elements or empty
     */
    private function getFileListChildren(string $parentValue, string $listName): array
    {
        if (strpos($listName, '.') !== false) {
            list ($module, $name) = explode('.', $listName);

            $moduleConfig = new ModuleConfig(ConfigType::MIGRATION(), $module);
            $fields = $moduleConfig->parse();
            $fields = json_decode(json_encode($fields), true);

            $fieldInfo = $fields[$name];
            $type = $fieldInfo['type'];
            preg_match('/\((.*)\)/', $type, $match);
            $listName = !empty($match[1]) ? $match[1] : null;
        }

        $moduleConfig = new ModuleConfig(ConfigType::LISTS(), null, $listName);
        $listData = $moduleConfig->parse()->items;
        $result = json_decode(json_encode($listData), true);

        $list = [];
        foreach ($result as $value => $item) {
            if ($value !== $parentValue || empty($item['children'])) {
                continue;
            }

            foreach ($item['children'] as $childValue => $childItem) {
                array_push($list, ['value' => $childValue]);
            }
        }

        return $list;
    }

    /**
     * processChildren method
     *
     * @param string $value to search
     * @param string $type - dblist of list stored in files
     * @param string $listName to find children in
     *
     * @return mixed[] with childen items or empty
     */
    private function processChildren(string $value, string $type = self::LIST_TYPE_DBLIST, string $listName = ''): array
    {
        $result = [];
        $list = [];

        if ($type == static::LIST_TYPE_DBLIST) {
            $table = TableRegistry::get('CsvMigrations.DblistItems');
            $query = $table->find('all', [
                'conditions' => ['value' => $value],
            ]);
            $item = $query->first();

            $list = $this->getDbListChildren($item['id'], $table);
        } elseif ($type == static::LIST_TYPE_FILELIST) {
            $list = $this->getFileListChildren($value, $listName);
        }

        foreach ($list as $item) {
            array_push($result, $item['value']);
            $ret = $this->processChildren($item['value'], $type, $listName);

            if (!empty($ret)) {
                $result = array_merge($result, $ret);
            }
        }

        return $result;
    }
}
