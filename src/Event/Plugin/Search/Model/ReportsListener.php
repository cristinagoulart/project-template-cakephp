<?php

namespace App\Event\Plugin\Search\Model;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use Search\Event\EventName;

class ReportsListener implements EventListenerInterface
{
    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_DASHBOARDS_GET_REPORTS() => 'getReports'
        ];
    }

    /**
     * Get all reports configurations
     *
     * Used in <model>/report/<slug> method to get reports
     * from the ini file on the dynamic model/table.
     *
     * @param \Cake\Event\Event $event Event instance
     *
     * @return void
     */
    public function getReports(Event $event): void
    {
        $path = Configure::read('CsvMigrations.modules.path');

        $modules = Utility::findDirs($path);
        if (empty($modules)) {
            return;
        }

        $result = [];
        foreach ($modules as $module) {
            $mc = new ModuleConfig(ConfigType::REPORTS(), $module);
            $report = $mc->parseToArray();

            if (empty($report)) {
                continue;
            }

            $result[$module] = $report;
        }

        $event->result = $result;
    }
}
