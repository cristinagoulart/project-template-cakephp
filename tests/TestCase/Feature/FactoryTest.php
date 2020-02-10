<?php

namespace App\Test\TestCase\Feature;

use App\Feature\Factory;
use App\Feature\FeatureInterface;
use App\Feature\Type\BaseFeature;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * App\Feature\Factory Test Case
 */
class FactoryTest extends TestCase
{
    /**
     * For now we just make sure the Factory can be initialized, no assertions are made.
     *
     */
    public function testInit(): void
    {
        $reflection = new \ReflectionProperty(Factory::class, 'initialized');
        $reflection->setAccessible(true);
        $reflection->setValue(new \stdClass(), false);

        Factory::init();
        $this->assertInstanceOf(BaseFeature::class, Factory::get('A feature'));

        // cached
        Factory::init();
        $this->assertInstanceOf(BaseFeature::class, Factory::get('A feature'));

        $this->assertTrue(Factory::isInitialized());
    }

    public function testGet(): void
    {
        $reflection = new \ReflectionProperty(Factory::class, 'initialized');
        $reflection->setAccessible(true);
        $reflection->setValue(new \stdClass(), false);

        $feature = Factory::get('Foobar');
        $this->assertInstanceOf(FeatureInterface::class, $feature);
        $this->assertInstanceOf(BaseFeature::class, $feature);

        $feature = Factory::get('Not Existing Feature');
        $this->assertInstanceOf(BaseFeature::class, $feature);
    }

    public function testGetWithException(): void
    {
        $this->expectException(\RuntimeException::class);

        $featureName = 'InvalidFeatureName';
        $config = Configure::readOrFail('Features');
        $config[$featureName] = ['active' => true];

        Configure::write('Features', $config);

        Factory::get($featureName);
    }

    public function testGetList(): void
    {
        $features = Factory::getList();
        $this->assertTrue(is_array($features));
        $this->assertNotEmpty($features);

        $features = Factory::getList('Foobar');
        $this->assertEmpty($features);

        Configure::write('Features', []);

        $features = Factory::getList();
        $this->assertTrue(is_array($features));
        $this->assertEmpty($features);
    }
}
