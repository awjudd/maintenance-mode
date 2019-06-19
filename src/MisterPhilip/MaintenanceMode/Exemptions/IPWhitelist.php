<?php

namespace MisterPhilip\MaintenanceMode\Exemptions;

use Config;
use Request;

/**
 * Class IPWhitelist
 *
 * Checks to see if the user's IP matches any of the IPs whitelisted in the configuration
 *
 * @package MisterPhilip\MaintenanceMode
 */
class IPWhitelist extends MaintenanceModeExemption
{
    /**
     * Execute the exemption check
     *
     * @return bool
     */
    public function isExempt()
    {
        $authorizedIPs = $this->app['config']->get('maintenancemode.exempt-ips', []);
        $useProxy = $this->app['config']->get('maintenancemode.exempt-ips-proxy', false);
        $userIP = $this->app['request']->getClientIp($useProxy);

        if (is_array($authorizedIPs) && in_array($userIP, $authorizedIPs)) {
            return true;
        }

        // We did not have a match
        return false;
    }
}
