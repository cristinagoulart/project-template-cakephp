<?php

namespace App\Test\TestCase\Event;

use App\Event\Component\SettingsListener;
use App\Settings\DbConfig;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SettingsListenerTest extends TestCase
{
    public $fixtures = [
        'app.users',
        'app.settings',
    ];

    public function setUp()
    {
        parent::setUp();

        TableRegistry::getTableLocator()->clear();
        $config = TableRegistry::getTableLocator()->exists('LogAudit') ? [] : ['className' => 'App\Model\Table\SettingsTable'];
        TableRegistry::getTableLocator()->get('Settings', $config);
    }

    public function testUserOverwritesAppSettings() : void
    {
        Cache::clear(false, 'settings');
        $userId = '00000000-0000-0000-0000-000000000001';
        $listener = new SettingsListener();

        Configure::config('dbconfig', new DbConfig());
        Configure::load('Settings', 'dbconfig', true);
        $this->assertEquals('UTC', Configure::read('Timezone'));
        $listener->loadUserSettings(new Event('dummy'), $userId);
        $this->assertEquals('EEST', Configure::read('Timezone'));
    }
}
