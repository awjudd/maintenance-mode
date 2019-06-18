<?php

namespace MisterPhilip\MaintenanceMode\Exemptions;

use Config;

/**
 * Class EnvironmentWhitelist
 *
 * Checks to see if the Laravel environment matches any of the whitelisted environments in the configuration
 *
 * @package MisterPhilip\MaintenanceMode
 */
class EnvironmentWhitelist extends MaintenanceModeExemption
{
    /**
     * Execute the exemption check
     *
     * @return bool
     */
    public function isExempt()
    {
        $ignoreEnvs = $this->app['config']->get('maintenancemode.exempt-environments', []);

        if (is_array($ignoreEnvs) && in_array($this->app->environment(), $ignoreEnvs)) {
            return true;
        }

        // We did not have a match
        return false;
    }
}
