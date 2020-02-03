<?php

namespace App\Test\TestCase\Feature\Type;

use App\Feature\Factory;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class BaseFeatureTest extends TestCase
{
    public function testIsActive(): void
    {
        $feature = Factory::get('Base');

        $this->assertTrue($feature->isActive());
    }

    public function testIsSwaggerActive(): void
    {
        $this->assertTrue(Factory::get('Base')->isSwaggerActive());

        Configure::write('Features.Module' . DS . 'Things', ['active' => false]);
        $this->assertFalse(Factory::get('Module' . DS . 'Things')->isSwaggerActive());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testEnable(): void
    {
        Factory::get('Base')->enable();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDisable(): void
    {
        Factory::get('Base')->disable();
    }
}
