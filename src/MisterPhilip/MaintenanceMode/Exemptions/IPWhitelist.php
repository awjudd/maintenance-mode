<?php namespace MisterPhilip\MaintenanceMode\Exemptions;

use Config, Request;

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
        $authorizedIPs = Config::get('maintenancemode::config.exempt-ips', []);
        $useProxy = Config::get('maintenancemode::config.exempt-ips-proxy', false);
        $userIP = Request::getClientIp($useProxy);

        if(is_array($authorizedIPs) && in_array($userIP, $authorizedIPs))
        {
            return true;
        }

        // We did not have a match
        return false;
    }
}