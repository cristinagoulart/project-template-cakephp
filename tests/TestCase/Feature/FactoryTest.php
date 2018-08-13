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
     */
    public function testInit()
    {
        Factory::init();
    }

    public function testGet()
    {
        $feature = Factory::get('Foobar');
        $this->assertInstanceOf(FeatureInterface::class, $feature);
        $this->assertInstanceOf(BaseFeature::class, $feature);

        $feature = Factory::get('Not Existing Feature');
        $this->assertInstanceOf(BaseFeature::class, $feature);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetExceptionNotString()
    {
        $result = Factory::get(true);
    }

    public function testGetList()
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
