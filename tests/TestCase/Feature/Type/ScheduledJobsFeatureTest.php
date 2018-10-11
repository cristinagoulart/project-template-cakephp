<?php
namespace App\Test\TestCase\Feature\Type;

use App\Feature\Factory;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class ScheduledJobsFeatureTest extends TestCase
{
    public function testIsActive(): void
    {
        $config = Configure::read('Features.Module/ScheduledJobs');
        $feature = Factory::get('Module/ScheduledJobs');

        $this->assertEquals($config['active'], $feature->isActive());
    }

    public function testEnable(): void
    {
        $feature = Factory::get('Module/ScheduledJobs');
        $feature->enable();

        $config = Configure::read('RolesCapabilities.accessCheck.skipControllers');
        $value = 'App\\Controller\\ScheduledJobsController';

        $this->assertFalse(array_search($value, $config));
    }
}
