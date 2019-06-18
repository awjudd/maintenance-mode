<?php

namespace SampleApp\Listeners;

use MisterPhilip\MaintenanceMode\Events\MaintenanceModeEnabled;

use CheckItOnUs\StatusPage;

/**
 * Class UpdateStatusPageMaintenanceStarted
 *
 * This uses the CheckItOnUs Statuspage SDK (https://github.com/checkitonus/php-statuspage-sdk) to update a
 * Statuspage (https://www.statuspage.io) that maintenance has started.
 *
 * This is assuming a little bit of setup has been done already, e.g.:
 *  - A config file has been added for the statuspage with an API key (and component ID)
 *  - A statuspage and component has been setup, and the component ID set in the config file
 */
class UpdateStatusPageMaintenanceStarted
{
    public function handle(MaintenanceModeEnabled $maintenanceMode)
    {
        $server = new StatusPage\Server([
            'api_key' => config('statuspage.key'),
        ]);

        StatusPage\Component::on($server)
            ->findById(config('statuspage.component'))
            ->setStatus(Component::UNDER_MAINTENANCE)
            ->update();
    }
}
