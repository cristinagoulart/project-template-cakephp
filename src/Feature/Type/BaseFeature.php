<?php
namespace App\Feature\Type;

use App\Feature\Config;
use App\Feature\FeatureInterface;

class BaseFeature implements FeatureInterface
{
    /**
     * Feature config
     *
     * @var \App\Feature\Config
     */
    protected $config;

    /**
     * Contructor method.
     *
     * @param \App\Feature\Config $config Feature Config instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Feature status getter method.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->config->get('active');
    }

    /**
     * Feature status for whether the Swagger Doc should be generated
     *
     * @return bool
     */
    public function isSwaggerActive(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return !(bool)$this->config->get('disable_swagger');
    }

    /**
     * Feature enable method.
     *
     * @return void
     */
    public function enable(): void
    {
    }

    /**
     * Feature disable method.
     *
     * @return void
     */
    public function disable(): void
    {
    }
}
