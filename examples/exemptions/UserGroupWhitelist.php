<?php

namespace SampleApp\Exemptions;

use Auth;
use Config;

use MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption;

/**
 * Class UserGroupWhitelist
 *
 * Checks to see if the user's group is in the whitelist
 */
class UserGroupWhitelist extends MaintenanceModeExemption
{
    /**
     * Execute the exemption check
     *
     * @return bool
     */
    public function isExempt()
    {
        /*
         * This would grab the exempted routes from the maintenance.php config file
         *
         * An example of this file:
         * return [
         *   'exemptions' => [
         *     'user-groups' => [ 1, 2, 3 ],
         *   ],
         * ];
         */

        $exemptGroups = $this->app['config']->get('maintenancemode.exemptions.user-groups', []);
        $currentUser = $this->app['auth']->user();
        if (is_array($exemptGroups) && $currentUser !== null) {
            // Grab the current user group IDs
            $currentUserGroups = $currentUser->groups->modelKeys();

            // Check to see if the user is within this group
            if ((is_array($currentUserGroups) && count(array_intersect($currentUserGroups, $exemptGroups))) ||
                !is_array($currentUserGroups) && in_array($currentUserGroups, $exemptGroups)) {
                // The user matched the whitelist, they will NOT see the maintenance page
                return true;
            }
        }

        // The current user did not match the whitelist, therefore they MIGHT see the maintenance page (depending on other exemptions)
        return false;
    }
}
