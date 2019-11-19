<?php

namespace App\Feature\Type\Module;

use App\Feature\Type\BaseFeature;
use Cake\Core\Configure;

abstract class AbstractModuleFeature extends BaseFeature
{
    /**
     * Feature enable method.
     *
     * @return void
     */
    public function enable(): void
    {
        $name = explode(DS, $this->config->get('name'));
        $name = end($name);

        $config = Configure::read('RolesCapabilities.accessCheck.skipControllers');

        $value = 'App\\Controller\\' . $name . 'Controller';

        $key = array_search($value, $config);
        if (false !== $key) {
            unset($config[$key]);
        }

        Configure::write('RolesCapabilities.accessCheck.skipControllers', $config);
    }

    /**
     * Feature disable method.
     *
     * @return void
     */
    public function disable(): void
    {
        $name = explode(DS, $this->config->get('name'));
        $name = end($name);

        $config = Configure::read('RolesCapabilities.accessCheck.skipControllers');
        $config[] = 'App\\Controller\\' . $name . 'Controller';

        Configure::write('RolesCapabilities.accessCheck.skipControllers', $config);
    }
}
