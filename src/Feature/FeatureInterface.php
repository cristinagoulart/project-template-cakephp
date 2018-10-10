<?php
namespace App\Feature;

interface FeatureInterface
{
    /**
     * Contructor method.
     *
     * @param \App\Feature\Config $config Feature Config instance
     */
    public function __construct(Config $config);

    /**
     * Feature status getter method.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Feature status for whether the Swagger Doc should be generated
     *
     * @return bool
     */
    public function isSwaggerActive();

    /**
     * Feature disable method.
     *
     * @return void
     */
    public function enable();

    /**
     * Feature disable method.
     *
     * @return void
     */
    public function disable();
}
