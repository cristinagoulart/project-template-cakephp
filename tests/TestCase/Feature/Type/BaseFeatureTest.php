<?php
namespace App\Test\TestCase\Feature\Type;

use App\Feature\Factory;
use Cake\TestSuite\TestCase;

class BaseFeatureTest extends TestCase
{
    public function testIsActive(): void
    {
        $feature = Factory::get('Base');

        $this->assertTrue($feature->isActive());
    }
}
